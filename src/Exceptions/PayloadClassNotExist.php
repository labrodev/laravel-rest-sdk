<?php

declare(strict_types=1);

namespace Labrodev\RestSdk\Exceptions;

use Exception;

/**
 * Exception thrown when a specified payload class does not exist.
 *
 * This exception is used to signal that an attempt
 * was made to use a payload class that could not be found or does not exist
 * in the current context or scope.
 */
class PayloadClassNotExist extends Exception
{
    /**
     * Factory method to create a new PayloadClassNotExist exception.
     *
     * @param string $payloadClass The name of the payload class that was not found.
     * @return self Returns an instance of PayloadClassNotExist with a message indicating
     * the missing payload class.
     */
    public static function make(string $payloadClass): self {
        return new self("Payload class does not exist `{$payloadClass}`");
    }
}
