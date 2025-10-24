<?php

declare(strict_types=1);

namespace Fulll\Domain\Fleet;

use Fulll\Domain\Exception\VehicleAlreadyParkedAtLocationException;
use Fulll\Domain\Exception\VehicleAlreadyRegisteredException;
use Fulll\Domain\Exception\VehicleNotRegisteredException;
use Fulll\Domain\ValueObject\Location;
use Fulll\Domain\Vehicle\VehicleId;

final class Fleet
{
    public function __construct(
        private FleetId $id,
        private array $vehicles = [],
        private array $locations = []
    ){}

    public function id(): FleetId
    {
        return $this->id;
    }

    public function registerVehicle(VehicleId $vehicleId): void
    {
        if ($this->hasVehicle($vehicleId)) {
            throw new VehicleAlreadyRegisteredException((string) $vehicleId);
        }

        $this->vehicles[(string) $vehicleId] = $vehicleId;
    }

    public function parkVehicle(VehicleId $vehicleId, Location $location): void
    {
        if (!$this->hasVehicle($vehicleId)) {
            throw new VehicleNotRegisteredException();
        }

        $key = (string) $vehicleId;

        if (isset($this->locations[$key]) && $this->locations[$key]->equals($location)) {
            throw new VehicleAlreadyParkedAtLocationException(
                sprintf('Vehicle %s already parked at this location.', $key)
            );
        }

        $this->locations[$key] = $location;
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

    public function locationOf(VehicleId $vehicleId): ?Location
    {
        return $this->locations[(string) $vehicleId] ?? null;
    }
}
