<?php

namespace Northstar\Http\Controllers;

use Illuminate\Http\Request;
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
        // Get the app name from submission.
        if (! $request->has('app_name')) {
            throw new HttpException(400, 'Missing required information.');
        }

        $key = ApiKey::create([
            'app_id' => $request->get('app_name'),
        ]);

        return $this->respond($key, 201);
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     * @throws NotFoundHttpException
     */
    public function show($id)
    {
        // Find the user.
        $key = ApiKey::where('id', $id)->get();
        if (! $key->isEmpty()) {
            return $this->respond($key);
        }

        throw new NotFoundHttpException('The resource does not exist.');
    }

    /**
     * Delete an API key resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
    }
}
