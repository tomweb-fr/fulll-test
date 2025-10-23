<?php

declare(strict_types=1);

namespace Fulll\Domain\Vehicle;

use Fulll\Domain\ValueObject\Location;
use Fulll\Domain\Exception\VehicleAlreadyParkedAtLocationException;

final class Vehicle
{
    private VehicleId $id;
    private ?Location $location = null;

    public function __construct(VehicleId $id, ?Location $location = null)
    {
        $this->id = $id;
        $this->location = $location;
    }

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
