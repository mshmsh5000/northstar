<?php

namespace Northstar\Http\Controllers\Legacy;

use Illuminate\Http\Request;
use Northstar\Models\Client;
use Northstar\Http\Transformers\Legacy\KeyTransformer;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Northstar\Http\Controllers\Controller;

class KeyController extends Controller
{
    /**
     * @var KeyTransformer
     */
    protected $transformer;

    public function __construct(KeyTransformer $transformer)
    {
        $this->transformer = $transformer;

        $this->middleware('role:admin');
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
            'app_id' => 'required|unique:clients,client_id',
            'scope' => 'array|scope', // @see Scope::validateScopes
        ]);

        $key = Client::create($request->all());

        return $this->item($key, 201);
    }

    /**
     * Display the specified resource.
     * GET /keys/:client_secret
     *
     * @return \Illuminate\Http\Response
     * @throws NotFoundHttpException
     */
    public function show($client_secret)
    {
        $client = Client::where('client_secret', $client_secret)->first();

        if (! $client) {
            throw new NotFoundHttpException('The resource does not exist.');
        }

        return $this->item($client);
    }

    /**
     * Update the specified resource.
     * PUT /keys/:client_secret
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @throws HttpException
     */
    public function update($client_secret, Request $request)
    {
        $this->validate($request, [
            'scope' => 'array|scope', // @see Scope::validateScopes
        ]);

        $client = Client::where('client_secret', $client_secret)->firstOrFail();
        $client->update($request->all());

        return $this->item($client);
    }

    /**
     * Delete an API key resource.
     * DELETE /keys/:client_secret
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($client_secret)
    {
        $client = Client::where('client_secret', $client_secret)->firstOrFail();
        $client->delete();

        return $this->respond('Deleted key.', 200);
    }
}
