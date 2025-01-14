<?php

namespace Labrodev\RestAdapter\Services;

use Boka\Tw\Exceptions\PayloadClassNotExist;
use Boka\Tw\Exceptions\TokenMissed;
use Boka\Tw\Services\Client;
use Labrodev\RestAdapter\Contracts\PayloadTypeAware;
use Labrodev\RestAdapter\Factories\PayloadFactory;

class Adapter
{
    public function __construct(
        public string $apiUrl,
        public array $authHeaders = [],
        public int $requestLimitAttempts = 5,
    ) {
    }

    /**
     * @throws PayloadClassNotExist
     * @throws TokenMissed
     */
    public function request(
        PayloadTypeAware $payloadType,
        $headers = [],
        $routeParameters = [],
        $queryParameters = [],
    ) {
        $payload = PayloadFactory::make(payloadType: $payloadType);

        $payload->setRequestLimitAttempts($this->requestLimitAttempts);

        foreach ($this->authHeaders as $header => $value) {
            $payload->addHeader($header, $value);
        }

        foreach ($headers as $header => $value) {
            $payload->addHeader($header, $value);
        }

        $payload->setQueryParameters($queryParameters);

        foreach ($routeParameters as $routeParameter) {
            $payload->addRouteParameter($routeParameter);
        }

        $client = Client::make(payload: $payload);

        return $client->execute();
    }
}
