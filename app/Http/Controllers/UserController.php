<?php

namespace Northstar\Http\Controllers;

use Illuminate\Http\Request;
use League\Fractal\Resource\Item;
use Northstar\Http\Transformers\UserTransformer;
use Northstar\Services\Phoenix;
use Northstar\Models\User;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class UserController extends Controller
{
    /**
     * Phoenix Drupal API wrapper.
     * @var Phoenix
     */
    protected $phoenix;

    /**
     * @var UserTransformer
     */
    protected $transformer;

    public function __construct(Phoenix $phoenix)
    {
        $this->phoenix = $phoenix;

        $this->transformer = new UserTransformer();

        $this->middleware('key:user', ['except' => 'destroy']);
        $this->middleware('key:admin', ['only' => 'destroy']);
    }

    /**
     * Display a listing of the resource.
     * GET /users
     * GET /users?attr1=value1&attr2=value2&...
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Create an empty User query, which we can either filter (below)
        // or paginate to retrieve all user records.
        $query = $this->newQuery(User::class);

        $query = $this->filter($query, $request->query('filter'), User::$indexes);
        $query = $this->search($query, $request->query('search'), User::$indexes);

        return $this->paginatedCollection($query, $request);
    }

    /**
     * Store a newly created resource in storage.
     * POST /users
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @throws UnauthorizedHttpException
     */
    public function store(Request $request)
    {
        $check = $request->only('email', 'mobile');
        $input = $request->all();

        $user = false;
        $found_user = false;

        // Does this user exist already?
        if ($request->has('email')) {
            $found_user = User::where('email', '=', $check['email'])->first();
        } elseif ($request->has('mobile')) {
            $found_user = User::where('mobile', '=', $check['mobile'])->first();
        }

        if ($found_user && password_verify($input['password'], $found_user->password)) {
            $user = $found_user;
        }

        // If there is no user found, create a new one.
        if (! $user) {
            $user = new User;

            // This validation might not be needed, the only validation happening right now
            // is for unique email or phone numbers, and that should return a user
            // from the query above.
            $this->validate($request, [
                'email' => 'email|max:60|unique:users|required_without:mobile',
                'mobile' => 'unique:users|required_without:email',
            ]);
        }
        // Update or create the user from all the input.
        try {
            $user->fill($input);

            // Do we need to forward this user to drupal?
            // If query string exists, make a drupal user.
            // @TODO: we can't create a Drupal user without an email. Do we just create an @mobile one like we had done previously?
            if ($request->has('create_drupal_user') && $request->has('password') && ! $user->drupal_id) {
                try {
                    $drupal_id = $this->phoenix->register($user, $request->get('password'));
                    $user->drupal_id = $drupal_id;
                } catch (\Exception $e) {
                    // If user already exists, find the user to get the uid.
                    if ($e->getCode() == 403) {
                        try {
                            $drupal_id = $drupal->getUidByEmail($user->email);
                            $user->drupal_id = $drupal_id;
                        } catch (\Exception $e) {
                            // @TODO: still ok to just continue and allow the user to be saved?
                        }
                    }
                }
            }
            if ($request->has('created_at')) {
                $user->created_at = $request->get('created_at');
            }

            $user->save();

            // Log the user in & attach their session token to response.
            $token = $user->login();
            $user->session_token = $token->key;

            return $this->item($user);
        } catch (\Exception $e) {
            return $this->respond($e, 401);
        }
    }

    /**
     * Display the specified resource.
     * GET /users/:term/:id
     *
     * @param $term - string
     *   term to search by (eg. mobile, drupal_id, id, email, etc)
     * @param $id - string
     *  the actual value to search for
     *
     * @return \Illuminate\Http\Response
     * @throws NotFoundHttpException
     */
    public function show($term, $id)
    {
        // Find the user.
        $user = User::where($term, $id)->first();

        if (! $user) {
            throw new NotFoundHttpException('The resource does not exist.');
        }

        return $this->item($user);
    }

    /**
     * Update the specified resource in storage.
     * PUT /users/:term/:id
     *
     * @param $term - string
     *   term to search by (eg. drupal_id, _id)
     * @param $id - string
     *   the actual value to search for
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function update($term, $id, Request $request)
    {
        $input = $request->all();

        $user = User::where($term, $id)->first();

        if ($user instanceof User) {
            foreach ($input as $key => $value) {
                if ($key == 'interests') {
                    $interests = array_map('trim', explode(',', $value));
                    $user->push('interests', $interests, true);
                } // Only update attribute if value is non-null.
                elseif (isset($key) && ! is_null($value)) {
                    $user->$key = $value;
                }
            }
        $this->validate($request, [
            'email' => 'email|max:60|unique:users,email,'.$user->id.',_id',
            'mobile' => 'unique:users,mobile,'.$user->id.',_id',
        ]);

            $user->save();

            return $this->item($user, 202);
        }

        throw new NotFoundHttpException('The resource does not exist.');
    }

    /**
     * Delete a user resource.
     * DELETE /users/:id
     *
     * @param $id - User ID
     * @return \Illuminate\Http\Response
     * @throws NotFoundHttpException
     */
    public function destroy($id)
    {
        $user = User::where('_id', $id)->first();

        if ($user instanceof User) {
            $user->delete();

            return $this->respond('No Content.');
        } else {
            throw new NotFoundHttpException('The resource does not exist.');
        }
    }
}
