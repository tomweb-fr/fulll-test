<?php

declare(strict_types=1);

namespace Fulll\App\QueryHandler;

use Fulll\App\Query\GetFleetQuery;
use Fulll\Domain\Repository\FleetRepositoryInterface;
use Fulll\Domain\Fleet\Fleet;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetFleetQueryHandler implements QueryHandlerInterface
{
    public function __construct(private FleetRepositoryInterface $fleetRepository) {}

    public function handle(object $query): ?Fleet
    {
        if (!$query instanceof GetFleetQuery) {
            throw new \InvalidArgumentException('Expected ' . GetFleetQuery::class);
        }

        return $this->__invoke($query);
    }

    public function __invoke(GetFleetQuery $query): ?Fleet
    {
        return $this->fleetRepository->find($query->fleetId());
    }
}
