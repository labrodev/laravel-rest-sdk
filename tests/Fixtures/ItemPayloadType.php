<?php

declare(strict_types=1);

namespace Labrodev\RestSdk\Tests\Fixtures;

use Labrodev\RestSdk\Contracts\PayloadTypeAware;

enum ItemPayloadType: string implements PayloadTypeAware
{
    case Show = ItemShowPayload::class;

    public function getPayloadClass(): string
    {
        return $this->value;
    }
}
