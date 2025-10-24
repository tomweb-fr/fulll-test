<?php

declare(strict_types=1);

namespace Fulll\Domain\Fleet;

final class FleetId
{
    private function __construct(private readonly string $id)
    {}

    public static function fromString(string $id): self
    {
        return new self($id);
    }

    public static function generate(string $prefix = 'fleet-'): self
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
