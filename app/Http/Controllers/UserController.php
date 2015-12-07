<?php

namespace Northstar\Http\Controllers;

use Illuminate\Http\Request;
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

    public function __construct(Phoenix $phoenix)
    {
        $this->phoenix = $phoenix;

        $this->middleware('key:user');
        $this->middleware('auth');

        $this->middleware('user');
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
        $except_list = User::$indexes;
        array_push($except_list, 'page');
        $inputs = $request->except($except_list);
        $users = User::where($inputs);

        // Query for multiple ids
        $query_ids = [];
        foreach (User::$indexes as $id_key) {
            if ($request->has($id_key)) {
                $str_ids = $request->get($id_key);
                $arr_ids = explode(',', $str_ids);
                $query_ids[$id_key] = $arr_ids;
            }
        }

        foreach ($query_ids as $id_key => $id_value) {
            $users->whereIn($id_key, $id_value);
        }

        $response = $this->respondPaginated($users, $inputs);

        return $response;
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
                'email' => 'email|unique:users|required_without:mobile',
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

            return $this->respond($user);
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
        $user = User::where($term, $id)->get();
        if (! $user->isEmpty()) {
            return $this->respond($user);
        }

        throw new NotFoundHttpException('The resource does not exist.');
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

            $user->save();

            return $this->respond($user, 202);
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

    /**
     * Create the response for when a request fails validation. Overrides the ValidatesRequests trait.
     *
     * @param Request $request
     * @param array $errors
     * @return \Illuminate\Http\Response
     * @throws UnauthorizedHttpException
     */
    protected function buildFailedValidationResponse(Request $request, array $errors)
    {
        $error_message = '';
        if (count($errors) > 0) {
            foreach ($errors as $e) {
                foreach ($e as $message) {
                    $error_message .= $message.' ';
                }
            }

            throw new UnauthorizedHttpException(null, trim($error_message));
        } else {
            return parent::buildFailedValidationResponse($request, $errors);
        }
    }
}
