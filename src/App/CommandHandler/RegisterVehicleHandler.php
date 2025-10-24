<?php

declare(strict_types=1);

namespace Fulll\App\CommandHandler;

use Fulll\App\Command\RegisterVehicle;
use Fulll\Domain\Fleet\FleetRepositoryInterface;
use Fulll\Domain\Exception\FleetNotFoundException;

final class RegisterVehicleHandler
{
    public function __construct(private FleetRepositoryInterface $repo) {}

    public function __invoke(RegisterVehicle $command): void
    {
        $fleetId = $command->fleetId;
        $vehicleId = $command->vehicleId;

        $fleet = $this->repo->find($fleetId);
        if ($fleet === null) {
            throw new FleetNotFoundException((string) $fleetId);
        }

        $fleet->registerVehicle($vehicleId);
        $this->repo->save($fleet);
    }
}
