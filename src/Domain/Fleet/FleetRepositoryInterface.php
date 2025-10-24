<?php

declare(strict_types=1);

namespace Fulll\Domain\Fleet;

interface FleetRepositoryInterface
{
    public function save(Fleet $fleet): void;
    public function find(FleetId $fleetId): ?Fleet;
}
