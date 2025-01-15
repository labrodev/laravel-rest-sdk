<?php

declare(strict_types=1);

namespace Labrodev\RestSdk\Contracts;

use Illuminate\Http\Client\Response;

interface ClientAware
{
    /**
     * @return Response
     */
    public function execute(): Response;
}
