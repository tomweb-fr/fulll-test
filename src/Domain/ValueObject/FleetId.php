<?php

declare(strict_types=1);

namespace Fulll\Domain\ValueObject;

final class FleetId
{
    private function __construct(private readonly string $id)
    {
    }

    public static function fromString(string $id): self
    {
        $id = trim($id);
        if ($id === '') {
            throw new \InvalidArgumentException('Fleet id cannot be empty.');
        }
        return new self($id);
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
