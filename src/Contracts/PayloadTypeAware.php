<?php

namespace Labrodev\RestAdapter\Contracts;

interface PayloadTypeAware
{
    /**
     * @return string
     */
    public function getPayloadClass(): string;
}
