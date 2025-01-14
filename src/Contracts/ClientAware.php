<?php

declare(strict_types=1);

namespace Labrodev\RestAdapter\Contracts;

interface ClientAware
{
    public function execute(): array;
}
