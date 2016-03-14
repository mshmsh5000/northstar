<?php

namespace Northstar\Http\Controllers;

use Illuminate\Http\Request;
use Northstar\Auth\Registrar;
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

        $this->middleware('key:admin', ['except' => ['index', 'show']]);
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
     */
    public function store(Request $request)
    {
        // This is an "upsert" endpoint (so it will either create a new user, or
        // update a user if one with a matching email or mobile number is found).
        // So, does this user exist already?
        $user = $this->registrar->resolve($request->only('email', 'mobile'));

        // Validate format & index uniqueness (excluding the profile being updated, if one exists)
        $existingId = isset($user->id) ? $user->id : 'null';
        $request = $this->registrar->normalize($request);
        $this->validate($request, [
            'email' => 'email|max:60|unique:users,email,'.$existingId.',_id|required_without:mobile',
            'mobile' => 'unique:users,mobile,'.$existingId.',_id|required_without:email',
        ]);

        $user = $this->registrar->register($request->all(), $user);

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

        return $this->item($user);
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
     * @param string $term - term to search by (eg. mobile, drupal_id, id, email, etc)
     * @param string $id - the actual value to search for
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function update($term, $id, Request $request)
    {
        $user = User::where($term, $id)->first();
        if (! $user) {
            throw new NotFoundHttpException('The resource does not exist.');
        }

        $request = $this->registrar->normalize($request);
        $this->validate($request, [
            'email' => 'email|max:60|unique:users,email,'.$user->id.',_id',
            'mobile' => 'unique:users,mobile,'.$user->id.',_id',
        ]);

        $user = $this->registrar->register($request->all(), $user);

        // Should we try to make a Drupal account for this user?
        if ($request->has('create_drupal_user') && $request->has('password') && ! $user->drupal_id) {
            $user = $this->registrar->createDrupalUser($user, $request->input('password'));
            $user->save();
        }

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
        $user = User::where('_id', $id)->first();

        if (! $user) {
            throw new NotFoundHttpException('The resource does not exist.');
        }

        $user->delete();

        return $this->respond('No Content.');
    }
}
