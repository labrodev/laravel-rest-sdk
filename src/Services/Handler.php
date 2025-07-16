<?php

declare(strict_types=1);

namespace Labrodev\RestSdk\Services;

use Labrodev\RestSdk\Contracts\HandlerAware;
use Labrodev\RestSdk\Contracts\PayloadTypeAware;
use Labrodev\RestSdk\Services\Payload;
use Labrodev\RestSdk\Factories\PayloadFactory;
use Labrodev\RestSdk\Exceptions\ApiUrlMissed;
use Labrodev\RestSdk\Exceptions\MethodUnsupported;
use Labrodev\RestSdk\Exceptions\PayloadClassNotExist;
use Labrodev\RestSdk\Exceptions\RequestAttemptLimitReached;
use Labrodev\RestSdk\Exceptions\RequestFailed;
use Illuminate\Http\Client\Response;

class Handler implements HandlerAware
{
    /**
     * @param string $apiUrl
     * @param array $authHeaders
     * @param int $requestLimitAttempts
     */
    public function __construct(
        public string $apiUrl,
        public array $authHeaders = [],
        public int $requestLimitAttempts = 1,
    ) {
    }

    /**
     * @param PayloadTypeAware $payloadType
     * @param array $headers
     * @param array $routeParameters
     * @param array $queryParameters
     * @param array $body
     * @return Response
     * @throws MethodUnsupported
     * @throws PayloadClassNotExist
     * @throws RequestAttemptLimitReached
     * @throws RequestFailed
     */
    public function run(
        PayloadTypeAware $payloadType,
        array $headers = [],
        array $routeParameters = [],
        array $queryParameters = [],
        array $body = []
    ): Response {
        $payload = PayloadFactory::make(payloadType: $payloadType);

        foreach ($this->authHeaders as $header => $value) {
            $payload->addHeader($header, $value);
        }

        foreach ($headers as $header => $value) {
            $payload->addHeader($header, $value);
        }

        $payload->setBody($body);

        $payload->setQueryParameters($queryParameters);

        foreach ($routeParameters as $routeParameter) {
            $payload->addRouteParameter($routeParameter);
        }

        $client = Client::make(payload: $payload, apiUrl: $this->apiUrl);

        return $client->execute();
    }
}
