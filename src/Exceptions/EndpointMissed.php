<?php

declare(strict_types=1);

namespace Labrodev\RestAdapter\Exceptions;

use Exception;

/**
 * Exception indicates a missing endpoint for a specific payload class.
 */
class EndpointMissed extends Exception
{
    /**
     * Creates an instance highlighting the missing endpoint.
     *
     * @param string $payloadClass The class missing its endpoint.
     * @return self Instance with a descriptive error message.
     */
    public static function make(string $payloadClass): self {
        return new self("Endpoint is missed in `{$payloadClass}`");
    }
}
