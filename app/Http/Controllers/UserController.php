<?php

namespace Northstar\Http\Controllers;

use Illuminate\Http\Request;
use Northstar\Auth\Registrar;
use Northstar\Auth\Role;
use Northstar\Exceptions\NorthstarValidationException;
use Northstar\Http\Transformers\UserTransformer;
use Northstar\Services\Phoenix;
use Northstar\Models\User;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserController extends Controller
{
    /**
     * Phoenix Drupal API wrapper.
     * @var Phoenix
     */
    protected $phoenix;

    /**
     * The registrar.
     * @var Registrar
     */
    protected $registrar;

    /**
     * @var UserTransformer
     */
    protected $transformer;

    /**
     * UserController constructor.
     * @param Phoenix $phoenix
     * @param Registrar $registrar
     */
    public function __construct(Phoenix $phoenix, Registrar $registrar)
    {
        $this->phoenix = $phoenix;
        $this->registrar = $registrar;

        $this->transformer = new UserTransformer();

        $this->middleware('role:admin,staff', ['except' => ['show']]);
    }

    /**
     * Display a listing of the resource.
     * GET /users
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Create an empty User query, which we can either filter (below)
        // or paginate to retrieve all user records.
        $query = $this->newQuery(User::class);

        $filters = $request->query('filter');
        $query = $this->filter($query, normalize('credentials', $filters), User::$indexes);

        $searches = $request->query('search');
        $query = $this->search($query, normalize('credentials', $searches), User::$indexes);

        return $this->paginatedCollection($query, $request);
    }

    /**
     * Store a newly created resource in storage.
     * POST /users
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @throws NorthstarValidationException
     */
    public function store(Request $request)
    {
        // This endpoint will upsert by default (so it will either create a new user, or
        // update a user if one with a matching index field is found).
        $existingUser = $this->registrar->resolve($request->only('id', 'email', 'mobile', 'drupal_id', 'facebook_id'));

        // If `?upsert=false` and a record already exists, return a custom validation error.
        if (! filter_var($request->query('upsert', 'true'), FILTER_VALIDATE_BOOLEAN) && $existingUser) {
            throw new NorthstarValidationException(['id' => ['A record matching one of the given indexes already exists.']], $existingUser);
        }

        // Normalize input and validate the request
        $request = normalize('credentials', $request);
        $this->registrar->validate($request, $existingUser);

        // Makes sure we can't "upsert" a record to have a changed index if already set.
        // @TODO: There must be a better way to do this...
        foreach (User::$uniqueIndexes as $index) {
            if ($request->has($index) && ! empty($existingUser->{$index}) && $request->input($index) !== $existingUser->{$index}) {
                app('stathat')->ezCount('upsert conflict');
                logger('attempted to upsert an existing index', [
                    'index' => $index,
                    'new' => $request->input($index),
                    'existing' => $existingUser->{$index},
                ]);

                throw new NorthstarValidationException([$index => ['Cannot upsert an existing index.']], $existingUser);
            }
        }

        $upserting = ! is_null($existingUser);
        $user = $this->registrar->register($request->except('role'), $existingUser);

        // Optionally, allow setting a custom "created_at" (useful for back-filling from other services).
        if ($request->has('created_at')) {
            $user->created_at = $request->input('created_at');
            $user->save();
        }

        // Should we try to make a Drupal account for this user?
        if ($request->has('create_drupal_user') && $request->has('password') && ! $user->drupal_id) {
            $user = $this->registrar->createDrupalUser($user, $request->input('password'));
            $user->save();
        }

        $code = $upserting ? 200 : 201;

        return $this->item($user, $code);
    }

    /**
     * Display the specified resource.
     * GET /users/:term/:id
     *
     * @param string $term - term to search by (eg. mobile, drupal_id, id, email, etc)
     * @param string $id - the actual value to search for
     *
     * @return \Illuminate\Http\Response
     * @throws NotFoundHttpException
     */
    public function show($term, $id)
    {
        // Restrict username/email/mobile/facebook_id profile lookup to admin or staff.
        if (in_array($term, ['username', 'email', 'mobile', 'facebook_id'])) {
            Role::gate(['admin', 'staff']);
        }

        $user = $this->registrar->resolveOrFail([$term => $id]);

        return $this->item($user);
    }

    /**
     * Update the specified resource in storage.
     * PUT /users/:term/:id
     *
     * @param string $term - term to search by (eg. mobile, drupal_id, id, email, etc)
     * @param string $id - the actual value to search for
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function update($term, $id, Request $request)
    {
        $user = $this->registrar->resolveOrFail([$term => $id]);

        // Normalize input and validate the request
        $request = normalize('credentials', $request);
        $this->registrar->validate($request, $user);

        // Only admins can change the role field.
        if ($request->has('role') && $request->input('role') !== 'user') {
            Role::gate(['admin']);
        }

        $user->fill($request->all());

        // Should we try to make a Drupal account for this user?
        if ($request->has('create_drupal_user') && $request->has('password') && ! $user->drupal_id) {
            $user = $this->registrar->createDrupalUser($user, $request->input('password'));
        }

        $user->save();

        return $this->item($user);
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
        $user = User::findOrFail($id);
        $user->delete();

        return $this->respond('No Content.');
    }
}
