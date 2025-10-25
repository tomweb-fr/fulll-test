<?php

declare(strict_types=1);

namespace Fulll\App\CommandHandler;

use Fulll\App\Command\ParkVehicle;
use Fulll\Domain\Exception\FleetNotFoundException;
use Fulll\Domain\Repository\FleetRepositoryInterface;

final readonly class ParkVehicleHandler
{
    public function __construct(private FleetRepositoryInterface $repo) {}

    public function __invoke(ParkVehicle $command): void
    {
        $fleetId = $command->fleetId;
        $vehicleId = $command->vehicleId;
        $location = $command->location;

        $fleet = $this->repo->find($fleetId);
        if ($fleet === null) {
            throw new FleetNotFoundException((string) $fleetId);
        }

        $fleet->parkVehicle($vehicleId, $location);
        $this->repo->save($fleet);
    }
}
