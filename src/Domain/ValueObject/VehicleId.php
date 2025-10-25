<?php

declare(strict_types=1);

namespace Fulll\Domain\ValueObject;

final class VehicleId
{
    private function __construct(private readonly string $id) {}

    public static function fromString(string $id): self
    {
        $id = trim($id);
        if ($id === '') {
            throw new \InvalidArgumentException('Vehicle id cannot be empty.');
        }
        return new self($id);
    }

    public function __toString(): string
    {
        return $this->id;
    }

    public function equals(VehicleId $other): bool
    {
        return $this->id === (string) $other;
    }
}
