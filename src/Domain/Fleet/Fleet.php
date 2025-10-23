<?php

declare(strict_types=1);

namespace Fulll\Domain\Fleet;

use Fulll\Domain\Vehicle\VehicleId;
use Fulll\Domain\Exception\VehicleAlreadyRegisteredException;

final class Fleet
{
    private string $id;
    private array $vehicles = [];

    public function __construct(string $id)
    {
        if (trim($id) === '') {
            throw new \InvalidArgumentException('Fleet id cannot be empty.');
        }
        $this->id = $id;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function registerVehicle(VehicleId $vehicle): void
    {
        $key = (string) $vehicle;
        if (isset($this->vehicles[$key])) {
            throw new VehicleAlreadyRegisteredException(sprintf('Vehicle %s already registered in fleet %s', $key, $this->id));
        }
        $this->vehicles[$key] = true;
    }

    public function hasVehicle(VehicleId $vehicle): bool
    {
        return isset($this->vehicles[(string) $vehicle]);
    }

    /**
     * @return VehicleId[]
     */
    public function vehicles(): array
    {
        return array_map(fn(string $id) => VehicleId::fromString($id), array_keys($this->vehicles));
    }
}
