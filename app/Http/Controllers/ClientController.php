<?php

namespace Northstar\Http\Controllers;

use Illuminate\Http\Request;
use Northstar\Http\Transformers\ClientTransformer;
use Northstar\Models\Client;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ClientController extends Controller
{
    /**
     * @var ClientTransformer
     */
    protected $transformer;

    public function __construct(ClientTransformer $transformer)
    {
        $this->transformer = $transformer;

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
        $keys = Client::all();

        return $this->collection($keys);
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
            'scope' => 'array|scope', // @see ApiKey::validateScopes
        ]);

        $key = Client::create($request->all());

        return $this->item($key, 201);
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
        $key = Client::where('api_key', $id)->first();
        if (! $key) {
            throw new NotFoundHttpException('The resource does not exist.');
        }

        return $this->item($key);
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
            'scope' => 'array|scope', // @see ApiKey::validateScopes
        ]);

        $key = Client::where('api_key', $key)->firstOrFail();
        $key->update($request->all());

        return $this->item($key);
    }

    /**
     * Delete an API key resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($key)
    {
        $key = Client::where('api_key', $key)->firstOrFail();
        $key->delete();

        return $this->respond('Deleted key.', 200);
    }
}
