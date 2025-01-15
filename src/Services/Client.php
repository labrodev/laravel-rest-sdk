<?php

declare(strict_types=1);

namespace Labrodev\RestSdk\Services;

use Labrodev\RestSdk\Exceptions\ApiUrlMissed;
use Labrodev\RestSdk\Exceptions\MethodUnsupported;
use Labrodev\RestSdk\Exceptions\RequestAttemptLimitReached;
use Labrodev\RestSdk\Exceptions\RequestFailed;
use Labrodev\RestSdk\Contracts\ClientAware;
use Labrodev\RestSdk\Contracts\PayloadAware;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class Client implements ClientAware
{
    /**
     * Counter of request attempts
     *
     * Need to add logic of avoiding request loop when new access token also forbidden
     *
     * @var integer
     */
    private int $requestAttempts = 0;

    private const SLEEP_IN_SECONDS = 5;

    /**
     * @param PayloadAware $payload
     * @param string $apiUrl
     * @param int $requestAttemptsLimit
     */
    public function __construct(
        private PayloadAware $payload,
        private string $apiUrl,
        private int $requestAttemptsLimit = 1
    ) {
    }

    /**
     * @param PayloadAware $payload
     * @param string $apiUrl
     * @param int $requestAttemptsLimit
     * @return self
     */
    public static function make(PayloadAware $payload, string $apiUrl, int $requestAttemptsLimit = 1): self
    {
        return new self(payload: $payload, apiUrl: $apiUrl, requestAttemptsLimit: $requestAttemptsLimit);
    }

    /**
     * @throws RequestAttemptLimitReached
     * @throws MethodUnsupported
     * @throws RequestFailed
     */
    public function execute(): Response
    {
        $httpRequest = Http::withHeaders($this->payload->getHeaders());

        $requestUrl = $this->fetchRequestUrl(
            apiUrl: $this->apiUrl,
            endpoint: $this->payload->getEndpointWithQueryParameters()
        );

        $response = match($this->payload->getMethod()) {
            PayloadAware::METHOD_GET => $httpRequest->get($requestUrl),
            PayloadAware::METHOD_POST => $httpRequest->post($requestUrl, $this->payload->getBody()),
            PayloadAware::METHOD_PUT => $httpRequest->put($requestUrl, $this->payload->getBody()),
            default => throw MethodUnsupported::make($this->payload->getMethod())
        };

        if (!$response->successful()) {
            match ($response->status()) {
                SymfonyResponse::HTTP_REQUEST_TIMEOUT => $this->handleTimeout(),
                default => $this->handleOtherStatuses($response)
            };
        }

        return $response;
    }

    /**
     * Constructs the full request URL from the API base URL and endpoint.
     *
     * @param  string  $apiUrl    The base URL of the API.
     * @param  string  $endpoint  The specific endpoint URL.
     * @return string The full URL for the request.
     */
    private function fetchRequestUrl(string $apiUrl, string $endpoint): string
    {
        return rtrim($apiUrl, '/') . '/' . ltrim($endpoint, '/');
    }

    /**
     * Attempts to handle a Timeout response by retrying the request until limit of attempts reached.
     *
     * @return Response The response from the retried request.
     * @throws RequestAttemptLimitReached
     * @throws RequestFailed|MethodUnsupported
     */
    private function handleTimeout(): Response
    {
        if ($this->requestAttempts > $this->requestAttemptsLimit) {
            throw RequestAttemptLimitReached::make();
        }

        sleep(self::SLEEP_IN_SECONDS);

        $this->countRequestAttempt();

        return $this->execute();
    }

    /**
     * Method to handle request errors
     *
     * @param Response $response
     * @return void
     * @throws RequestFailed
     */
    private function handleOtherStatuses(Response $response): void
    {
        throw RequestFailed::make(
            payloadClass: get_class($this->payload),
            status: $response->status(),
            body: $response->body()
        );
    }

    /**
     * Method to count request attempt
     *
     * @return void
     */
    private function countRequestAttempt(): void
    {
        $this->requestAttempts++;
    }
}
