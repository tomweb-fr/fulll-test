<?php

declare(strict_types=1);

namespace Fulll\App\Query;

use Fulll\Domain\ValueObject\FleetId;

final readonly class GetFleetQuery
{
    public function __construct(private FleetId $fleetId) {}

    public static function fromString(string $fleetId): self
    {
        return new self(FleetId::fromString($fleetId));
    }

    public function fleetId(): FleetId
    {
        return $this->fleetId;
    }
}