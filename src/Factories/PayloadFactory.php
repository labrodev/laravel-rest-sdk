<?php

declare(strict_types=1);

namespace Labrodev\RestSdk\Factories;

use Labrodev\RestSdk\Exceptions\PayloadClassNotExist;
use Labrodev\RestSdk\Contracts\PayloadAware;
use Labrodev\RestSdk\Contracts\PayloadTypeAware;

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

        $payload = new $payloadClassName();

        if (!is_subclass_of($payload, PayloadAware::class)) {
            throw PayloadClassNotExist::make($payloadClassName);
        }

        return new $payload();
    }
}
