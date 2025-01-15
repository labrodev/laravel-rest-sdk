<?php

declare(strict_types=1);

namespace Labrodev\RestSdk\Exceptions;

use Exception;

/**
 * Exception indicates that a required HTTP method is not specified in a payload class.
 */
class MethodMissed extends Exception
{
    /**
     * Factory method for creating the exception with a message indicating the missing method.
     *
     * @param string $payloadClass The class name where the method is missing.
     * @return self The exception instance with a descriptive message.
     */
    public static function make(string $payloadClass): self {
        return new self("Method is missed in `{$payloadClass}`.");
    }
}
