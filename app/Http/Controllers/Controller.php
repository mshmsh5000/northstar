<?php namespace Northstar\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesCommands;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Database\Eloquent;
use Input;

abstract class Controller extends BaseController
{

    use DispatchesCommands, ValidatesRequests;

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

        return response()->json($response, $code, array(), JSON_UNESCAPED_SLASHES);
    }

    /**
     * Method to standardize paginated responses.
     *
     * @param $query - Eloquent query
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function respondPaginated($query, $inputs) {
        if (is_a($query, 'Illuminate\Database\Eloquent\Builder')) {
            $limit = Input::get('limit') ?: 20;
            $response = $query->paginate((int)$limit);
            $response->appends($inputs);
            return response()->json($response);
        }
    }

}
