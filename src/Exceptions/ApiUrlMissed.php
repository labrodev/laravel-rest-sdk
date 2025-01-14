<?php

declare(strict_types=1);

namespace Labrodev\RestAdapter\Exceptions;

use Exception;

/**
 * Exception thrown when the External API URL is missing from the application configuration.
 *
 * This exception is used to indicate that the necessary configuration for connecting
 * to the external API, specifically the API URL, has not been set. This is a critical
 * configuration that must be provided for the application to communicate with the API.
 */
class ApiUrlMissed extends Exception
{
    /**
     * Factory method to create an instance of the exception with a default message.
     *
     * This method simplifies the creation of the exception by providing a default
     * error message indicating that the API URL is missing from the configuration.
     *
     * @return self Returns an instance of the ApiUrlMissed exception.
     */
    public static function make(): self {
        return new self("API url is missed in configuration.");
    }
}
