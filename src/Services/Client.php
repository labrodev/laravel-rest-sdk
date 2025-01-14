<?php

declare(strict_types=1);

namespace Labrodev\RestAdapter\Services;

use Labrodev\RestAdapter\Exceptions\ApiUrlMissed;
use Labrodev\RestAdapter\Exceptions\RequestAttemptLimitReached;
use Labrodev\RestAdapter\Exceptions\RequestFailed;
use Labrodev\RestAdapter\Contracts\ClientAware;
use Labrodev\RestAdapter\Contracts\PayloadAware;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class Client implements ClientAware
{
    /**
     * The payload containing data for the request.
     *
     * @var PayloadAware
     */
    public PayloadAware $payload;

    /**
     * The base URL for the API.
     *
     * @var string
     */
    private string $apiUrl;

    /**
     * Counter of request attempts
     *
     * Need to add logic of avoiding request loop when new access token also forbidden
     *
     * @var integer
     */
    private int $requestAttempts = 0;

    /**
     * @var integer
     */
    private int $requestAttemptsLimit = 1;

    private int $refreshTokenAttempt = 0;

    private const SLEEP_IN_SECONDS = 5;

    /**
     * Initializes the client with a given payload.
     *
     * @param  PayloadAware $payload  The payload for the API request.
     * @throws ApiUrlMissed If the API URL is not defined in configuration.
     */
    public function __construct(PayloadAware $payload)
    {
        $this->apiUrl = config('tw.api_url') ?? throw ApiUrlMissed::make();
        $this->requestAttemptsLimit = config('tw.request_attempts_limit') ?? 1;
        $this->payload = $payload;
    }

    /**
     * Static method as factory to make an instance of current class
     *
     * @param PayloadInterface $payload
     * @return self
     */
    public static function make(PayloadInterface $payload): self
    {
        return new self($payload);
    }

    /**
     * Executes the API request based on the payload settings.
     *
     * @return array<mixed,mixed> The API response as an associative array.
     * @throws TokenMissed If the token is missing.
     */
    public function execute(): array
    {
        $token = $this->payload->getToken() ?? $this->refreshToken();

        return $this->makeRequest($token)->json();
    }

    /**
     * Makes the API request using the provided token
     * @return Response The API response.
     * @throws RequestFailed If the request fails.
     */
    private function makeRequest(): Response
    {
        $httpRequest = Http::withHeaders($this->payload->getHeaders());

        $requestUrl = $this->fetchRequestUrl(
            apiUrl: $this->apiUrl,
            endpoint: $this->payload->getEndpointWithQueryParameters()
        );

        $response = match($this->payload->getMethod()) {
            PayloadInterface::METHOD_GET => $httpRequest->get($requestUrl),
            PayloadInterface::METHOD_POST => $httpRequest->post($requestUrl, $this->payload->getBody()),
            PayloadInterface::METHOD_PUT => $httpRequest->put($requestUrl, $this->payload->getBody()),
            default => throw MethodUnsupported::make($this->payload->getMethod())
        };

        if ($response->successful()) {
            return $response;
        }

        return match ($response->status()) {
            SymfonyResponse::HTTP_REQUEST_TIMEOUT => $this->handleTimeout(),
            default => $this->handleOtherStatuses($response)
        };
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
     * @throws RequestFailed
     */
    private function handleTimeout(): Response
    {
        if ($this->refreshTokenAttempt > $this->requestAttemptsLimit) {
            throw RequestAttemptLimitReached::make();
        }

        sleep(self::SLEEP_IN_SECONDS);

        $this->countRequestAttempt();

        return $this->makeRequest();
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
            error: $response->json()['error'] ?? 'Unknow error'
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
