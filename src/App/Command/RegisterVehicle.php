<?php

declare(strict_types=1);

namespace Fulll\App\Command;

use Fulll\Domain\Fleet\FleetId;
use Fulll\Domain\Vehicle\VehicleId;

final readonly class RegisterVehicle
{
    public function __construct(
        public FleetId $fleetId,
        public VehicleId $vehicleId
    ) {}
}
