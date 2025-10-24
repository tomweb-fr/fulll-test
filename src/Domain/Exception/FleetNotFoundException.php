<?php

declare(strict_types=1);

namespace Fulll\Domain\Exception;

final class FleetNotFoundException extends \DomainException
{
    public function __construct(string $fleetId)
    {
        parent::__construct(sprintf('fleet-not-found: %s', $fleetId));
    }
}
