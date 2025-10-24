<?php

declare(strict_types=1);

namespace Fulll\Infra\InMemory;

use Fulll\Domain\Fleet\Fleet;
use Fulll\Domain\Fleet\FleetId;
use Fulll\Domain\Fleet\FleetRepositoryInterface;

final class FleetRepositoryInMemory implements FleetRepositoryInterface
{
    private array $fleets = [];

    public function save(Fleet $fleet): void
    {
        $this->fleets[(string) $fleet->id()] = $fleet;
    }

    public function find(FleetId $fleetId): ?Fleet
    {
        return $this->fleets[(string) $fleetId] ?? null;
    }
}
