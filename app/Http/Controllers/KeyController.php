<?php

namespace Northstar\Http\Controllers;

use Illuminate\Http\Request;
use Northstar\ApiKeyScopes;
use Northstar\Models\ApiKey;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class KeyController extends Controller
{
    public function __construct()
    {
        $this->middleware('key:admin');
    }

    /**
     * Display a listing of the resource.
     * GET /keys
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $keys = ApiKey::all();

        return $this->respond($keys);
    }

    /**
     * Store a newly created resource in storage.
     * POST /keys
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @throws HttpException
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'app_id' => 'required|unique:api_keys,app_id',
            'scope' => 'array|scope' // @see ApiKeyScopes::validate
        ]);

        $key = ApiKey::create($request->all());

        return $this->respond($key, 201);
    }

    /**
     * Display the specified resource.
     * GET /keys/:api_key
     *
     * @return \Illuminate\Http\Response
     * @throws NotFoundHttpException
     */
    public function show($id)
    {
        // Find the user.
        $key = ApiKey::where('api_key', $id)->first();
        if (! $key) {
            throw new NotFoundHttpException('The resource does not exist.');
        }

        return $this->respond($key);
    }

    /**
     * Update the specified resource.
     * PUT /keys/:api_key
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @throws HttpException
     */
    public function update($key, Request $request)
    {
        $this->validate($request, [
            'scope' => 'array|scope' // @see ApiKeyScopes::validate
        ]);

        $key = ApiKey::where('api_key', $key)->firstOrFail();
        $key->update($request->all());

        return $this->respond($key, 200);
    }

    /**
     * Delete an API key resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($key)
    {
        $key = ApiKey::where('api_key', $key)->firstOrFail();
        $key->delete();

        return $this->respond('Deleted key.', 200);
    }
}
