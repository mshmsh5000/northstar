<?php

namespace Northstar\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Northstar\Http\Controllers\Traits\FiltersRequests;
use Northstar\Http\Controllers\Traits\TransformsResponses;
use Illuminate\Http\Request;

abstract class Controller extends BaseController
{
    use DispatchesJobs, ValidatesRequests, FiltersRequests, TransformsResponses;

    /**
     * Create the response for when a request fails validation. Overrides the
     * `buildFailedValidationResponse` method from the `ValidatesRequests` trait.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $errors
     * @return \Illuminate\Http\Response
     */
    protected function buildFailedValidationResponse(Request $request, array $errors)
    {
        $response = [
            'code' => 422,
            'message' => 'Failed validation.',
            'errors' => $errors,
        ];

        return response()->json($response, 422);
    }
}
