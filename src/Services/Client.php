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

    public const DEFAULT_RETRY_DELAY_SECONDS = 5;

    /**
     * @param PayloadAware $payload
     * @param string $apiUrl
     * @param int $requestAttemptsLimit Maximum number of retries after a 408 Request Timeout response
     * @param int|null $connectTimeout Seconds to wait for a connection; null keeps Laravel's default
     * @param int|null $timeout Seconds to wait for a response; null keeps Laravel's default
     * @param int $retryDelaySeconds Seconds to sleep before retrying a 408; 0 retries immediately
     */
    public function __construct(
        private PayloadAware $payload,
        private string $apiUrl,
        private int $requestAttemptsLimit = 1,
        private ?int $connectTimeout = null,
        private ?int $timeout = null,
        private int $retryDelaySeconds = self::DEFAULT_RETRY_DELAY_SECONDS
    ) {
    }

    /**
     * @param PayloadAware $payload
     * @param string $apiUrl
     * @param int $requestAttemptsLimit
     * @param int|null $connectTimeout
     * @param int|null $timeout
     * @param int $retryDelaySeconds
     * @return self
     */
    public static function make(
        PayloadAware $payload,
        string $apiUrl,
        int $requestAttemptsLimit = 1,
        ?int $connectTimeout = null,
        ?int $timeout = null,
        int $retryDelaySeconds = self::DEFAULT_RETRY_DELAY_SECONDS
    ): self {
        return new self(
            payload: $payload,
            apiUrl: $apiUrl,
            requestAttemptsLimit: $requestAttemptsLimit,
            connectTimeout: $connectTimeout,
            timeout: $timeout,
            retryDelaySeconds: $retryDelaySeconds
        );
    }

    /**
     * @throws RequestAttemptLimitReached
     * @throws MethodUnsupported
     * @throws RequestFailed
     */
    public function execute(): Response
    {
        $httpRequest = Http::withHeaders($this->payload->getHeaders());

        if ($this->connectTimeout !== null) {
            $httpRequest->connectTimeout($this->connectTimeout);
        }

        if ($this->timeout !== null) {
            $httpRequest->timeout($this->timeout);
        }

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

        if ($response->successful()) {
            return $response;
        }

        if ($response->status() === SymfonyResponse::HTTP_REQUEST_TIMEOUT) {
            return $this->handleTimeout();
        }

        $this->handleOtherStatuses($response);
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
        if ($this->requestAttempts >= $this->requestAttemptsLimit) {
            throw RequestAttemptLimitReached::make();
        }

        if ($this->retryDelaySeconds > 0) {
            sleep($this->retryDelaySeconds);
        }

        $this->countRequestAttempt();

        return $this->execute();
    }

    /**
     * Method to handle request errors
     *
     * @param Response $response
     * @return never
     * @throws RequestFailed
     */
    private function handleOtherStatuses(Response $response): never
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
