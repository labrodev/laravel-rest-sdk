<?php

declare(strict_types=1);

namespace Labrodev\RestSdk\Tests;

use Labrodev\RestSdk\RestSdkServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    /**
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [RestSdkServiceProvider::class];
    }
}
