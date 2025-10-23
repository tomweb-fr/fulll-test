<?php

declare(strict_types=1);

namespace Fulll\Infra\InMemory;

use Fulll\Domain\Fleet\Fleet;
use Fulll\Domain\Fleet\FleetRepositoryInterface;

final class FleetRepositoryInMemory implements FleetRepositoryInterface
{
    private array $store = [];

    public function save(Fleet $fleet): void
    {
        $this->store[$fleet->id()] = $fleet;
    }

    public function find(string $fleetId): ?Fleet
    {
        return $this->store[$fleetId] ?? null;
    }
}
