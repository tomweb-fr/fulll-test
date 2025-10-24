<?php


declare(strict_types=1);

namespace Fulll\App\Handler;

use Fulll\App\Command\ParkVehicle;
use Fulll\Domain\Fleet\Fleet;
use Fulll\Domain\Fleet\FleetRepositoryInterface;

final class ParkVehicleHandler
{
    public function __construct(private FleetRepositoryInterface $repo){}

    public function __invoke(ParkVehicle $command): void
    {
        $fleetId = $command->fleetId();
        $vehicleId = $command->vehicleId();
        $location = $command->location();

        $fleet = $this->repo->find($fleetId) ?? new Fleet($fleetId);
        $fleet->parkVehicle($vehicleId, $location);
        $this->repo->save($fleet);
    }
}
