<?php

declare(strict_types=1);

namespace Fulll\Domain\Vehicle;

final class VehicleId
{
    private string $id;

    private function __construct(string $id)
    {
        if (trim($id) === '') {
            throw new \InvalidArgumentException('Vehicle id cannot be empty.');
        }
        $this->id = $id;
    }

    public static function fromString(string $id): self
    {
        return new self($id);
    }

    public static function generate(string $prefix = 'veh-'): self
    {
        return new self($prefix . uniqid());
    }

    public function equals(self $other): bool
    {
        return $this->id === $other->id;
    }

    public function __toString(): string
    {
        return $this->id;
    }
}
