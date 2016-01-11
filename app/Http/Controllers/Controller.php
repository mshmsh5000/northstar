<?php

namespace Northstar\Http\Controllers;

use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection as FractalCollection;
use League\Fractal\Resource\Item as FractalItem;
use League\Fractal\Serializer\DataArraySerializer;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Database\Eloquent;
use Illuminate\Http\Request;
use Input;

abstract class Controller extends BaseController
{
    use DispatchesJobs;

    use ValidatesRequests {
        buildFailedValidationResponse as traitBuildFailedValidationResponse;
    }

    /**
     * @var \League\Fractal\TransformerAbstract
     */
    protected $transformer;

    public function collection($collection, $code = 200, $meta = null, $transformer = null)
    {
        if(is_null($transformer)) {
            $transformer = $this->transformer;
        }

        $manager = new Manager(new DataArraySerializer());
        $resource = new FractalCollection($collection, $transformer, 'things');
        $response = $manager->createData($resource)->toArray();

        if($meta) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $code, [], JSON_UNESCAPED_SLASHES);
    }

    public function item($item, $code = 200, $meta = null, $transformer = null)
    {
        if(is_null($transformer)) {
            $transformer = $this->transformer;
        }

        $manager = new Manager(new DataArraySerializer());
        $resource = new FractalItem($item, $transformer, 'thing');
        $response = $manager->createData($resource)->toArray();

        if($meta) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $code, [], JSON_UNESCAPED_SLASHES);
    }

    /**
     * Method to standardize paginated responses.
     *
     * @param $query - Eloquent query
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function paginate($query, $request, $code = 200, $meta = null, $transformer = null)
    {
        if(is_null($transformer)) {
            $transformer = $this->transformer;
        }

        $paginator = $query->paginate((int) $request->query('limit', 20));

        $manager = new Manager(new DataArraySerializer());
        $resource = new FractalCollection($paginator->getCollection(), $transformer);

        $queryParams = array_diff_key($request->query(), array_flip(['page']));
        $paginator->appends($queryParams);

        $resource->setPaginator(new IlluminatePaginatorAdapter($paginator));
        $response = $manager->createData($resource)->toArray();

        if($meta) {
            // @TODO: Don't overwrite pagination though!
            $response['meta'] = $meta;
        }

        return response()->json($response, $code, [], JSON_UNESCAPED_SLASHES);
    }

    /**
     * Method to standardize responses sent from child controllers.
     *
     * @param mixed $data - Data to send in the response
     * @param int $code - Status code
     * @param string $status - When $data is a message string, this is the name of the object enclosing the message
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function respond($data, $code = 200, $status = 'success')
    {
        $response = [];
        if (is_string($data)) {
            $response[$status] = ['message' => $data];
        } elseif (is_object($data) || is_array($data)) {
            $response['data'] = $data;
        } else {
            $response = $data;
        }

        return response()->json($response, $code, [], JSON_UNESCAPED_SLASHES);
    }

    /**
     * Create the response for when a request fails validation. Overrides the ValidatesRequests trait.
     *
     * @param Request $request
     * @param array $errors
     * @return \Illuminate\Http\Response
     */
    protected function buildFailedValidationResponse(Request $request, array $errors)
    {
        return $this->traitBuildFailedValidationResponse($request, $errors);
    }
}
