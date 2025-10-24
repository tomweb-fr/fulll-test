<?php

declare(strict_types=1);

namespace Fulll\App\Handler;

use Fulll\App\Command\RegisterVehicle;
use Fulll\Domain\Fleet\FleetRepositoryInterface;
use Fulll\Domain\Fleet\Fleet;
use Fulll\Domain\Vehicle\VehicleId;

final readonly class RegisterVehicleHandler
{
    public function __construct(private FleetRepositoryInterface $repo) {}

    public function __invoke(RegisterVehicle $cmd): void
    {
        $fleet = $this->repo->find($cmd->fleetId) ?? new Fleet($cmd->fleetId);
        $fleet->registerVehicle($cmd->vehicleId);
        $this->repo->save($fleet);
    }
}
