<?php

declare(strict_types=1);

namespace Fulll\App\Command;

use Fulll\Domain\Fleet\FleetId;
use Fulll\Domain\Vehicle\VehicleId;
use Fulll\Domain\ValueObject\Location;

final readonly class ParkVehicle
{
    public function __construct(
        private FleetId   $fleetId,
        private VehicleId $vehicleId,
        private Location  $location
    ) {
    }

    public function fleetId(): FleetId
    {
        return $this->fleetId;
    }

    public function vehicleId(): VehicleId
    {
        return $this->vehicleId;
    }

    public function location(): Location
    {
        return $this->location;
    }
}
