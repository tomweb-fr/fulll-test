<?php

declare(strict_types=1);

namespace Fulll\App\CommandHandler;

use Fulll\App\Command\CreateFleet;
use Fulll\Domain\Fleet\Fleet;
use Fulll\Domain\Repository\FleetRepositoryInterface;
use Fulll\Domain\ValueObject\FleetId;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class CreateFleetHandler
{
    public function __construct(private FleetRepositoryInterface $repo) {}

    public function __invoke(CreateFleet $cmd): FleetId
    {
        $fleetId = $cmd->fleetId;

        if ($this->repo->find($fleetId) !== null) {
            throw new \RuntimeException('fleet-already-exists');
        }

        $fleet = new Fleet($fleetId);
        $this->repo->save($fleet);

        return $fleetId;
    }
}
