<?php

declare(strict_types=1);

namespace Fulll\Domain\Fleet;

use Fulll\Domain\Exception\VehicleAlreadyRegisteredException;
use Fulll\Domain\Vehicle\VehicleId;

final class Fleet
{
    public function __construct(
        private FleetId $id,
        private array $vehicles = []
    ){}

    public function id(): FleetId
    {
        return $this->id;
    }

    public function registerVehicle(VehicleId $vehicleId): void
    {
        $key = (string) $vehicleId;
        if (isset($this->vehicles[$key])) {
            throw new VehicleAlreadyRegisteredException(sprintf(
                'Vehicle %s already registered in fleet %s',
                $key,
                (string) $this->id
            ));
        }

        $this->vehicles[$key] = $vehicleId;
    }

    public function hasVehicle(VehicleId $vehicleId): bool
    {
        return isset($this->vehicles[(string) $vehicleId]);
    }

    /**
     * @return VehicleId[]
     */
    public function vehicles(): array
    {
        return array_values($this->vehicles);
    }
}
