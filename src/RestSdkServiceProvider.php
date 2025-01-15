<?php

declare(strict_types=1);

namespace Labrodev\RestSdk;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

/**
 * Service provider for the Tw package.
 *
 * This class is responsible for configuring the package within a Laravel application.
 * It registers configurations, views, routes, and other components necessary for the package.
 */
class RestSdkServiceProvider extends PackageServiceProvider
{
    /**
     * The name of the package.
     *
     * Used for configuration and other package-specific references.
     */
    public const PACKAGE_NAME = 'rest-sdk';

    /**
     * Configures the package using Spatie's package tools.
     *
     * This method defines how the package should be set up in the consuming application,
     * including registering the package's configuration file for publication.
     *
     * @param Package $package The package being configured.
     */
    public function configurePackage(Package $package): void
    {
        $packageName = self::PACKAGE_NAME;

        $package
            ->name($packageName);
    }
}
