<?php

declare(strict_types=1);

namespace Fulll\App\Command;

use Fulll\Domain\ValueObject\FleetId;

final class CreateFleet
{
    public function __construct(public readonly FleetId $fleetId) {}
}
