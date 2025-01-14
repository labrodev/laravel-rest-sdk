<?php

declare(strict_types=1);

namespace Labrodev\RestAdapter\Contracts;

interface AdapterAware
{
    public function execute(): array;
}
