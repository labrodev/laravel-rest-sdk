<?php

declare(strict_types=1);

namespace Labrodev\RestAdapter\Exceptions;

use Exception;

/**
 * Exception thrown when an unsupported HTTP method is attempted to be used.
 */
class MethodUnsupported extends Exception
{
    /**
     * Creates an instance of the exception for an unsupported method.
     *
     * @param string $method The HTTP method that is unsupported.
     * @return self An exception instance with a detailed error message.
     */
    public static function make(string $method): self {
        return new self("Method `{$method}` is unsupported.");
    }
}
