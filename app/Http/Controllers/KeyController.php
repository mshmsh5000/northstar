<?php

namespace Northstar\Http\Controllers;

use Northstar\Models\ApiKey;
use Input;
use Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class KeyController extends Controller
{
    /**
     * Display a listing of the resource.
     * GET /keys
     *
     * @return Response
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
     * @return Response
     * @throws HttpException
     */
    public function store()
    {
        // Get the app name from submission.
        if (Input::has('app_name')) {
            $app_name = Input::get('app_name');
            $key = new ApiKey();
            $key->app_id = snake_case(str_replace(' ', '', $app_name));
            $key->api_key = str_random(40);
            // Save new key.
            $key->save();

            return $this->respond($key);
        }

        throw new HttpException(400, 'Missing required information.');
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     * @throws NotFoundHttpException
     */
    public function show($id)
    {
        // Find the user.
        $key = Key::where('id', $id)->get();
        if (! $key->isEmpty()) {
            return $this->respond($key);
        }

        throw new NotFoundHttpException('The resource does not exist.');
    }

    /**
     * Delete a api key resource.
     * DELETE /key/:id
     *
     * @return Response
     */
    public function destroy($id)
    {
    }
}
