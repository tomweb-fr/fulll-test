<?php

declare(strict_types=1);

namespace Fulll\App\QueryHandler;

interface QueryHandlerInterface
{
    /**
     * Handle a query object and return the read model or null.
     *
     * @param object $query
     * @return mixed
     */
    public function handle(object $query): mixed;
}
