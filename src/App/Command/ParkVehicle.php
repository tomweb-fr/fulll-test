<?php

declare(strict_types=1);

namespace Fulll\App\Command;

use Fulll\Domain\Fleet\FleetId;
use Fulll\Domain\Vehicle\VehicleId;
use Fulll\Domain\ValueObject\Location;

final readonly class ParkVehicle
{
    public function __construct(
        public FleetId   $fleetId,
        public VehicleId $vehicleId,
        public Location  $location
    ) {
    }

}
