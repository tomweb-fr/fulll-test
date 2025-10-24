<?php

declare(strict_types=1);

namespace Fulll\Domain\Vehicle;

use Fulll\Domain\ValueObject\Location;
use Fulll\Domain\Exception\VehicleAlreadyParkedAtLocationException;

final class Vehicle
{
    public function __construct(
        private VehicleId $id,
        private ?Location $location = null
    ){}

    public function id(): VehicleId
    {
        return $this->id;
    }

    public function location(): ?Location
    {
        return $this->location;
    }

    public function park(Location $location): void
    {
        if ($this->location !== null && $this->location->equals($location)) {
            throw new VehicleAlreadyParkedAtLocationException(sprintf('Vehicle %s already parked at this location.', (string) $this->id));
        }
        $this->location = $location;
    }
}
