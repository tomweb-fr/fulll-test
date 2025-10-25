<?php

declare(strict_types=1);

namespace Fulll\App\Command;

use Fulll\Domain\ValueObject\FleetId;
use Fulll\Domain\ValueObject\Location;
use Fulll\Domain\ValueObject\VehicleId;

final readonly class ParkVehicle
{
    public function __construct(
        public FleetId   $fleetId,
        public VehicleId $vehicleId,
        public Location  $location
    ) {
    }

}
