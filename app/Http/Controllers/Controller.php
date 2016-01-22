<?php

namespace Northstar\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
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

    /**
     * Format & return a single item response.
     *
     * @param $item
     * @param int $code
     * @param array $meta
     * @param null $transformer
     * @return \Illuminate\Http\JsonResponse
     */
    public function item($item, $code = 200, $meta = [], $transformer = null)
    {
        if (is_null($transformer)) {
            $transformer = $this->transformer;
        }

        $resource = new FractalItem($item, $transformer, 'thing');
        $resource->setMeta($meta);

        $manager = new Manager(new DataArraySerializer());
        $response = $manager->createData($resource)->toArray();

        return response()->json($response, $code, [], JSON_UNESCAPED_SLASHES);
    }

    /**
     * Format & return a collection response.
     *
     * @param $collection
     * @param int $code
     * @param array $meta
     * @param null $transformer
     * @return \Illuminate\Http\JsonResponse
     */
    public function collection($collection, $code = 200, $meta = [], $transformer = null)
    {
        if (is_null($transformer)) {
            $transformer = $this->transformer;
        }

        $resource = new FractalCollection($collection, $transformer, 'things');
        $resource->setMeta($meta);

        $manager = new Manager(new DataArraySerializer());
        $response = $manager->createData($resource)->toArray();

        return response()->json($response, $code, [], JSON_UNESCAPED_SLASHES);
    }

    /**
     * Format & return a paginated collection response.
     *
     * @param $query - Eloquent query
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function paginatedCollection($query, $request, $code = 200, $meta = [], $transformer = null)
    {
        if (is_null($transformer)) {
            $transformer = $this->transformer;
        }

        $paginator = $query->paginate((int) $request->query('limit', 20));

        $resource = new FractalCollection($paginator->getCollection(), $transformer);
        $resource->setMeta($meta);

        $queryParams = array_diff_key($request->query(), array_flip(['page']));
        $paginator->appends($queryParams);
        $resource->setPaginator(new IlluminatePaginatorAdapter($paginator));

        $manager = new Manager(new DataArraySerializer());
        $response = $manager->createData($resource)->toArray();

        return response()->json($response, $code, [], JSON_UNESCAPED_SLASHES);
    }

    /**
     * Format & return a standard object, array, or string response.
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
     * Create a new query builder from the given Eloquent class, which can then be
     * filtered, searched, and/or paginated.
     *
     * @param string $class - Eloquent model class name
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function newQuery($class)
    {
        return (new $class)->newQuery();
    }

    /**
     * Limit results to users exactly matching a set of filters.
     *
     * @param $query
     * @param $filters
     * @param $indexes - Indexed fields (whitelisted for filtering)
     * @return mixed
     */
    public function filter($query, $filters, $indexes)
    {
        if (! $filters) {
            return $query;
        }

        // Requests may be filtered by indexed fields.
        $filters = array_intersect_key($filters, array_flip($indexes));

        // You can filter by multiple values, e.g. `filter[source]=agg,cgg`
        // to get records that have a source value of either `agg` or `cgg`.
        foreach ($filters as $filter => $values) {
            $values = explode(',', $values);

            // For the first `where` query, we want to limit results... from then on,
            // we want to append (e.g. `SELECT * WHERE _ OR WHERE _ OR WHERE _`)
            $firstWhere = true;
            foreach ($values as $value) {
                $query->where($filter, '=', $value, ($firstWhere ? 'and' : 'or'));
                $firstWhere = false;
            }
        }

        return $query;
    }

    /**
     * Limit results to users matching a set of search terms.
     *
     * @param $query - Query to apply search to
     * @param array $searches - Key/value array of fields and search terms
     * @param array $indexes - Indexed fields (whitelisted for search)
     * @return mixed
     */
    public function search($query, $searches, $indexes)
    {
        if (! $searches) {
            return $query;
        }

        // Searches may only be performed on indexed fields.
        $searches = array_intersect_key($searches, array_flip($indexes));

        // For the first `where` query, we want to limit results... from then on,
        // we want to append (e.g. `SELECT * WHERE _ OR WHERE _ OR WHERE _`)
        $firstWhere = true;
        foreach ($searches as $term => $value) {
            $query->where($term, 'like', '%'.$value.'%', ($firstWhere ? 'and' : 'or'));
            $firstWhere = false;
        }

        return $query;
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
