<?php

namespace Northstar\Http\Controllers\Traits;

use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection as FractalCollection;
use League\Fractal\Resource\Item as FractalItem;
use League\Fractal\Serializer\DataArraySerializer;

trait TransformsResponses
{
    /**
     * The default transformer.
     *
     * @var \League\Fractal\TransformerAbstract
     */
    protected $transformer;

    /**
     * Transform the given item or collection.
     *
     * @param $resource
     * @return \Illuminate\Http\JsonResponse
     */
    public function transform($resource, $code)
    {
        $manager = new Manager(new DataArraySerializer());
        $response = $manager->createData($resource)->toArray();

        return response()->json($response, $code, [], JSON_UNESCAPED_SLASHES);
    }

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

        return $this->transform($resource, $code);
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

        return $this->transform($resource, $code);
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

        $pages = (int) $request->query('limit', 20);
        $paginator = $query->paginate(min($pages, 100));

        $queryParams = array_diff_key($request->query(), array_flip(['page']));
        $paginator->appends($queryParams);

        $resource = new FractalCollection($paginator->getCollection(), $transformer);

        $resource->setMeta($meta);
        $resource->setPaginator(new IlluminatePaginatorAdapter($paginator));

        return $this->transform($resource, $code);
    }

    /**
     * Return a string as the API response.
     *
     * @param string $message - Message to send in the response
     * @param int $code - Status code
     * @param string $status - The name of the object enclosing the message
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function respond($message, $code = 200, $status = 'success')
    {
        $response = [
            $status => [
                'message' => $message,
            ],
        ];

        return response()->json($response, $code, [], JSON_UNESCAPED_SLASHES);
    }
}
