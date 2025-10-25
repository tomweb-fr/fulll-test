<?php

declare(strict_types=1);

namespace Fulll\Infra\Doctrine\Mapper;

use Fulll\Infra\Doctrine\Entity\FleetDoctrine;
use Fulll\Domain\Fleet\Fleet;
use Fulll\Domain\ValueObject\FleetId;
use Fulll\Domain\ValueObject\VehicleId;
use Fulll\Domain\ValueObject\Location;

final class FleetDoctrineMapper
{
    public static function toDomain(FleetDoctrine $entity): Fleet
    {
        $fleetId = FleetId::fromString($entity->getId());

        $vehicles = [];
        foreach ($entity->getVehicles() as $vidString) {
            $vehicles[(string) $vidString] = VehicleId::fromString((string) $vidString);
        }

        $locations = [];
        foreach ($entity->getLocations() as $vid => $storedLoc) {
            if ($storedLoc instanceof Location) {
                $locations[(string) $vid] = $storedLoc;
                continue;
            }

            if (is_array($storedLoc)) {
                $lat = isset($storedLoc['lat']) ? (float) $storedLoc['lat'] : null;
                $lon = isset($storedLoc['lon']) ? (float) $storedLoc['lon'] : null;
            } else {
                throw new \InvalidArgumentException('Unsupported location format for vehicle ' . $vid);
            }

            if ($lat === null || $lon === null) {
                throw new \InvalidArgumentException('Missing lat/lon for vehicle ' . $vid);
            }

            $locations[(string) $vid] = new Location($lat, $lon);
        }

        return new Fleet($fleetId, $vehicles, $locations);
    }

    public static function toEntity(Fleet $fleet): FleetDoctrine
    {
        $vehicles = [];
        foreach ($fleet->vehicles() as $v) {
            $vehicles[] = (string) $v;
        }

        $locations = [];
        foreach ($fleet->vehicles() as $v) {
            $loc = $fleet->locationOf($v);
            if ($loc !== null) {
                $locations[(string) $v] = $loc->toArray();
            }
        }

        return new FleetDoctrine((string) $fleet->id(), $vehicles, $locations);
    }
}
