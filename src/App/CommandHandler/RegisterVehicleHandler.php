<?php

declare(strict_types=1);

namespace Fulll\App\CommandHandler;

use Fulll\App\Command\RegisterVehicle;
use Fulll\Domain\Exception\FleetNotFoundException;
use Fulll\Domain\Repository\FleetRepositoryInterface;

final readonly class RegisterVehicleHandler
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
