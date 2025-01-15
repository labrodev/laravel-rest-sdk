<?php

declare(strict_types=1);

namespace Labrodev\RestSdk\Contracts;

use Illuminate\Http\Client\Response;

interface HandlerAware
{
    /**
     * @param PayloadTypeAware $payloadType
     * @param array $headers
     * @param array $routeParameters
     * @param array $queryParameters
     * @return Response
     */
    public function run(PayloadTypeAware $payloadType, array $headers = [], array $routeParameters = [], array $queryParameters = []): Response;
}
