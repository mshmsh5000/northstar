<?php

namespace Northstar\Http\Middleware;

use Closure;
use Northstar\Models\Campaign;

class CampaignResponseMiddleware
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        if (! is_object($response)) {
            return $response;
        }

        $statusCode = $response->getStatusCode();
        $response = $response->getData();

        if (is_array($response->data)) {
            foreach ($response->data as $campaign) {
                Campaign::populateAllAttributes($campaign);
            }
        } elseif (is_object($response->data)) {
            Campaign::populateAllAttributes($response->data);
        }

        return response()->json($response, $statusCode, [], JSON_UNESCAPED_SLASHES);
    }
}
