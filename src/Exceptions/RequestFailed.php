<?php

declare(strict_types=1);

namespace Labrodev\RestSdk\Exceptions;

use Exception;

/**
 * Exception thrown when an API request fails.
 *
 * This exception captures and conveys details of a failed request, including
 * the associated payload class, HTTP status code, and an error message.
 */
class RequestFailed extends Exception
{
    /**
     * Factory method for creating an instance of the exception with detailed information about the failure.
     *
     * @param string $payloadClass The class name of the payload associated with the failed request.
     * @param int $status The HTTP status code returned by the failed request.
     * @param string $body
     * @return self The exception instance populated with failure details.
     */
    public static function make(
        string $payloadClass,
        int $status,
        string $body
    ): self {
        return new self("Request failed for `{$payloadClass}`. Status: `{$status}`. Body: `{$body}`.");
    }
}
