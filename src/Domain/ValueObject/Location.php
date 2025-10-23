<?php


declare(strict_types=1);

namespace Fulll\Domain\ValueObject;

final class Location
{
    private float $lat;
    private float $lon;

    public function __construct(float $lat, float $lon)
    {
        if ($lat < -90.0 || $lat > 90.0) {
            throw new \InvalidArgumentException('Latitude must be between -90 and 90.');
        }
        if ($lon < -180.0 || $lon > 180.0) {
            throw new \InvalidArgumentException('Longitude must be between -180 and 180.');
        }
        $this->lat = $lat;
        $this->lon = $lon;
    }

    public function lat(): float
    {
        return $this->lat;
    }

    public function lon(): float
    {
        return $this->lon;
    }

    public function equals(self $other): bool
    {
        return $this->lat === $other->lat && $this->lon === $other->lon;
    }

    public function toArray(): array
    {
        return ['lat' => $this->lat, 'lon' => $this->lon];
    }
}
