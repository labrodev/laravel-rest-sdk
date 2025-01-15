<?php

declare(strict_types=1);

namespace Labrodev\RestSdk\Exceptions;

use Exception;

/**
 * Exception thrown when the maximum number of request attempts is reached.
 *
 * This exception is used to indicate that an operation has exceeded the allowed number of attempts,
 * such as when retrying a request due to a failure or an authentication issue.
 */
class RequestAttemptLimitReached extends Exception
{
    /**
     * Factory method to create an instance of the exception with a standard message.
     *
     * @return self Returns an instance of the RequestAttemptLimitReached exception.
     */
    public static function make(): self {
        return new self("Maximum number of request attempts has been reached.");
    }
}
