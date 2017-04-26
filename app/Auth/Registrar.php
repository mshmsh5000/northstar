<?php

namespace Northstar\Auth;

use Closure;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\Factory as Validation;
use Illuminate\Http\Request;
use Northstar\Exceptions\NorthstarValidationException;
use Northstar\Models\User;
use Northstar\Services\Phoenix;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;

class Registrar
{
    /**
     * Phoenix Drupal API wrapper.
     *
     * @var Phoenix
     */
    protected $phoenix;

    /**
     * Laravel's validation factory.
     *
     * @var Validation
     */
    protected $validation;

    /**
     * The hasher implementation.
     *
     * @var \Illuminate\Contracts\Hashing\Hasher
     */
    protected $hasher;

    /**
     * Registrar constructor.
     *
     * @param Phoenix $phoenix
     * @param Validation $validation
     * @param Hasher $hasher
     */
    public function __construct(Phoenix $phoenix, Validation $validation, Hasher $hasher)
    {
        $this->phoenix = $phoenix;
        $this->validation = $validation;
        $this->hasher = $hasher;
    }

    /**
     * Validate the given user and request.
     *
     * @param Request $request
     * @param User $user
     * @param array $additionalRules
     * @throws NorthstarValidationException
     */
    public function validate(Request $request, User $user = null, array $additionalRules = [])
    {
        $fields = normalize('credentials', $request->all());

        $existingId = isset($user->id) ? $user->id : 'null';
        $rules = [
            'email' => 'email|unique:users,email,'.$existingId.',_id|required_without:mobile',
            'mobile' => 'mobile|unique:users,mobile,'.$existingId.',_id|required_without:email',
            'drupal_id' => 'unique:users,drupal_id,'.$existingId.',_id',
            'birthdate' => 'date',
            'country' => 'country',
            'password' => 'min:6|max:512',
        ];

        // If a user is provided, merge it into the request so we can validate
        // the state of the "updated" document, rather than just the changes.
        if ($user) {
            $fields = array_merge($user->toArray(), $fields);
        }

        $validator = $this->validation->make($fields, array_merge($rules, $additionalRules));

        if ($validator->fails()) {
            throw new NorthstarValidationException($validator->errors()->getMessages());
        }
    }

    /**
     * Resolve a user account from the given credentials. This will only
     * take into account unique indexes on the User.
     *
     * @param Request|array $credentials
     * @return User|null
     */
    public function resolve($credentials)
    {
        $credentials = normalize('credentials', $credentials);

        $matches = (new User)->query();

        // For the first `where` query, we want to limit results... from then on,
        // we want to append (e.g. `SELECT * WHERE _ OR WHERE _ OR WHERE _`)
        $firstWhere = true;
        foreach (User::$uniqueIndexes as $type) {
            if (isset($credentials[$type])) {
                $matches = $matches->where($type, '=', $credentials[$type], ($firstWhere ? 'and' : 'or'));
                $firstWhere = false;
            }
        }

        // If we did not query by any fields, return null.
        if ($firstWhere) {
            return null;
        }

        // If we found one user, return it.
        $matches = $matches->get();
        if (count($matches) == 1) {
            return $matches[0];
        }

        // If we can't conclusively resolve one user so return null.
        return null;
    }

    /**
     * Resolve a user account from the given credentials, or throw
     * an exception to trigger a 404 if not able to.
     *
     * @param $credentials
     * @return User|null
     */
    public function resolveOrFail($credentials)
    {
        $user = $this->resolve($credentials);

        if (! $user) {
            throw new ModelNotFoundException;
        }

        return $user;
    }

    /**
     * Validate a user against the given credentials. If the user has a Drupal
     * password & it matches, re-hash and save to the user document.
     *
     * @param UserContract|User $user
     * @param array $credentials
     * @return bool
     */
    public function validateCredentials($user, array $credentials)
    {
        if (! $user) {
            event(new \Illuminate\Auth\Events\Failed($user, $credentials));

            return false;
        }

        if ($this->hasher->check($credentials['password'], $user->password)) {
            event(new \Illuminate\Auth\Events\Login($user, false));

            return true;
        }

        if (! $user->password && DrupalPasswordHash::check($credentials['password'], $user->drupal_password)) {
            // If this user has a Drupal-hashed password, rehash it, remove the
            // Drupal password field from the user document, and save the user.
            $user->password = $credentials['password'];
            $user->save();

            event(new \Illuminate\Auth\Events\Login($user, false));

            return true;
        }

        // Well, looks like we couldn't authenticate...
        event(new \Illuminate\Auth\Events\Failed($user, $credentials));

        return false;
    }

    /**
     * Create a new user.
     *
     * @param array $input - Profile fields
     * @param User $user - Optionally, user to update
     * @param Closure $customizer - Customize the user instance before saving.
     * @return User|null
     */
    public function register($input, $user = null, Closure $customizer = null)
    {
        // If there is no user provided, create a new one.
        if (! $user) {
            $user = new User;
        }

        $user->fill($input);
        $user->save();

        if (! is_null($customizer)) {
            $customizer($user);
        }

        // If this user doesn't have a `drupal_id`, try to make one.
        if (! $user->drupal_id) {
            $user = $this->createDrupalUser($user);
            $user->save();
        }

        return $user;
    }

    /**
     * Send a "welcome" email to the given user.
     *
     * @param $user
     */
    public function sendWelcomeEmail($user)
    {
        $this->phoenix->sendTransactional($user->id, 'register');
    }

    /**
     * Create a Drupal user for the given account.
     *
     * @param User $user
     * @return mixed
     */
    public function createDrupalUser($user)
    {
        try {
            $drupal_id = $this->phoenix->createDrupalUser($user);
            $user->drupal_id = $drupal_id;
        } catch (ClientException $e) {
            // If user already exists (403 Forbidden), try to find the user to get the UID.
            if ($e->getCode() === 403) {
                $drupal_id = $this->phoenix->getDrupalIdForNorthstarUser($user);
                $user->drupal_id = $drupal_id;
            }

            // Since getDrupalIdForNorthstarUser may still return null, track that here.
            if (empty($user->drupal_id)) {
                logger('Encountered error when creating Drupal user', ['user' => $user, 'error' => $e]);
                app('stathat')->ezCount('error creating drupal uid for user');
            }
        } catch (ServerException $e) {
            logger('Encountered error when creating Drupal user', ['user' => $user, 'error' => $e]);
            app('stathat')->ezCount('error creating drupal uid for user');
        }

        return $user;
    }
}
