<?php

namespace Labrodev\RestSdk\Contracts;

interface PayloadTypeAware
{
    /**
     * @return string
     */
    public function getPayloadClass(): string;
}
