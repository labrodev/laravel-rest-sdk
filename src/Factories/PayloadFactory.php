<?php

declare(strict_types=1);

namespace Labrodev\RestAdapter\Factories;

use Boka\Tw\Enums\PayloadType;
use Boka\Tw\Exceptions\PayloadClassNotExist;
use Labrodev\RestAdapter\Contracts\PayloadAware;
use Labrodev\RestAdapter\Contracts\PayloadTypeAware;

class PayloadFactory
{
    /**
     * @param PayloadTypeAware $payloadType
     * @return PayloadAware
     * @throws PayloadClassNotExist
     */
    public static function make(PayloadTypeAware $payloadType): PayloadAware
    {
        $payloadClassName = $payloadType->getPayloadClass();

        if (!class_exists($payloadClassName)) {
            throw PayloadClassNotExist::make($payloadClassName);
        }

        return new $payloadClassName();
    }
}
