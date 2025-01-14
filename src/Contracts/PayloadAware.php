<?php

declare(strict_types=1);

namespace Labrodev\RestAdapter\Contracts;

/**
 * Interface for defining the structure and capabilities of payloads used in API requests.
 */
interface PayloadAware
{
    /**
     * The header name for specifying content type in HTTP requests.
     */
    public const CONTENT_TYPE = 'Content-Type';

    /**
     * The MIME type for JSON content, used as a value for the Content-Type header in HTTP requests.
     */
    public const APPLICATION_JSON = 'application/json';

    /**
     * Specifies the HTTP GET method for requests.
     */
    public const METHOD_GET = 'get';

    /**
     * Specifies the HTTP POST method for requests.
     */
    public const METHOD_POST = 'post';

    /**
     * Specifies the HTTP PUT method for requests.
     */
    public const METHOD_PUT = 'put';

    /**
     * Retrieves the headers for the HTTP request.
     *
     * @return array An associative array of headers.
     */
    public function getHeaders(): array;

    /**
     * Adds a header to the HTTP request.
     *
     * @param string $key The header name.
     * @param string $value The header value.
     */
    public function addHeader(string $key, string $value): void;

    /**
     * Retrieves the endpoint for the HTTP request.
     *
     * @return string The request endpoint.
     */
    public function getEndpoint(): string;

    /**
     * Retrieves the body of the HTTP request.
     *
     * @return array The request body as an associative array.
     */
    public function getBody(): array;

    /**
     * Sets the body of the HTTP request.
     *
     * @param array $body The request body as an associative array.
     */
    public function setBody(array $body): void;

    /**
     * Retrieves the HTTP method for the request.
     *
     * @return string The HTTP method.
     */
    public function getMethod(): string;

    /**
     * @param mixed $routeParameter
     * @return void
     */
    public function addRouteParameter(mixed $routeParameter): void;

    /**
     * @param int $requestAttemptsLimit
     * @return void
     */
    public function setRequestAttemptsLimit(int $requestAttemptsLimit): void;

    /**
     * @return int
     */
    public function fetchRequestAttemptsLimit(): int;
}
