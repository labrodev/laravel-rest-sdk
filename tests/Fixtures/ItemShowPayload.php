<?php

declare(strict_types=1);

namespace Labrodev\RestSdk\Tests\Fixtures;

use Labrodev\RestSdk\Services\Payload;

class ItemShowPayload extends Payload
{
    protected function fetchEndpoint(): string
    {
        return 'items';
    }

    protected function fetchMethod(): string
    {
        return self::METHOD_GET;
    }
}
