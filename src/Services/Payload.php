<?php

declare(strict_types=1);

namespace Labrodev\RestAdapter\Services;

use Labrodev\RestAdapter\Contracts\PayloadAware;
use Labrodev\RestAdapter\Exceptions\EndpointMissed;

/**
 * Abstract class representing a generic payload for API requests.
 *
 * This class provides the foundational attributes and methods required to construct
 * and manage the payload for an API request. Implementations must define specific
 * endpoint and method details.
 */
abstract class Payload implements PayloadAware
{
    /**
     * @var array The body of the HTTP request.
     */
    private array $body = [];

    /**
     * @var array The headers of the HTTP request.
     */
    private array $headers = [];

    /**
     * @var array Query parameters of the HTTP request
     */
    private array $queryParameters = [];

    /**
     * @var ?string The endpoint of the HTTP request.
     */
    private ?string $endpoint = null;

    /**
     * @var ?string The HTTP method of the request.
     */
    private ?string $method = null;

    /**
     * @var int
     */
    private int $requestAttemptsLimit;

    /**
     * Initializes the payload with the supplied Supplier entity and fetches necessary credentials.
     */
    public function __construct()
    {
        $this->endpoint = $this->fetchEndpoint();
        $this->method = $this->fetchMethod();
        $this->headers = $this->fetchDefaultHeaders();
    }

    /**
     * Gets the current set of HTTP request headers.
     *
     * @return array<string,mixed> The request headers.
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Adds or updates a header for the HTTP request.
     *
     * @param string $key The name of the header.
     * @param string $value The value of the header.
     */
    public function addHeader(string $key, string $value): void
    {
        $this->headers[$key] = $value;
    }

    /**
     * Gets the endpoint for the HTTP request.
     *
     * @return string The request endpoint.
     */
    public function getEndpoint(): string
    {
        if (!$this->endpoint) {
            throw EndpointMissed::make(static::class);
        }

        return $this->endpoint;
    }

    /**
     * @param PayloadAware $payload
     * @return void
     */
    public function setAuthPayload(PayloadAware $payload): void
    {
        $this->authPayload = $payload;
    }

    /**
     * @return PayloadAware|null
     */
    public function getAuthPayload(): ?PayloadAware
    {
        return $this->authPayload;
    }

    /**
     * Gets the endpoint with query parameters
     *
     * @return string The request endpoint.
     */
    public function getEndpointWithQueryParameters(): string
    {
        $queryParameters = $this->getQueryParameters();

        if (!empty($queryParameters)) {
            $query = '?' . http_build_query($this->getQueryParameters());
        } else {
            $query = '';
        }

        return $this->endpoint . $query;
    }

    /**
     * @param mixed $routeParameter
     * @return void
     */
    public function addRouteParameter(mixed $routeParameter): void
    {
        $this->endpoint = sprintf('%s/%s',
            $this->fetchEndpoint(),
            $routeParameter
        );
    }

    /**
     * Gets the body of the HTTP request.
     *
     * @return array<mixed,mixed> The request body.
     */
    public function getBody(): array
    {
        return $this->body;
    }

    /**
     * Gets the query parameters of the HTTP request.
     *
     * @return array<string,mixed> Query parameters
     */
    public function getQueryParameters(): array
    {
        return $this->queryParameters;
    }

    /**
     * @param array $queryParameters<string,mixed>
     * @return void
     */
    public function setQueryParameters(array $queryParameters): void
    {
        $this->queryParameters = $queryParameters;
    }

    /**
     * Sets the body for the HTTP request.
     *
     * @param array $body The request body.
     */
    public function setBody(array $body): void
    {
        $this->body = $body;
    }

    /**
     * Gets the HTTP method for the request.
     *
     * @return string The request method.
     */
    public function getMethod(): string
    {
        if (!$this->method) {
            throw MethodMissed::make(static::class);
        }

        return $this->method;
    }

    /**
     * @param int $requestAttemptsLimit
     * @return void
     */
    public function setRequestAttemptsLimit(int $requestAttemptsLimit): void
    {
        $this->requestAttemptsLimit = $requestAttemptsLimit;
    }

    /**
     * @param int $requestAttemptsLimit
     * @return void
     */
    public function fetchRequestAttemptsLimit(): int
    {
        return $this->requestAttemptsLimit;
    }

    /**
     * Fetch default headers
     *
     * @return array<string,string>
     */
    protected function fetchDefaultHeaders(): array
    {
        return [
            PayloadAware::CONTENT_TYPE => PayloadAware::APPLICATION_JSON
        ];
    }

    /**
     * Abstract method that must be implemented by subclasses to define the specific API endpoint.
     *
     * @return string The API endpoint.
     */
    abstract protected function fetchEndpoint(): string;

    /**
     * Abstract method that must be implemented by subclasses to define the HTTP method for the request.
     *
     * @return string The HTTP method (e.g., GET, POST, PUT).
     */
    abstract protected function fetchMethod(): string;
}
