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
        foreach ($entity->getVehicles() as $k => $v) {
            $vidString = self::idToString($k, $v);
            $vehicles[$v] = VehicleId::fromString($v);
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
        foreach ($fleet->vehicles() as $k => $v) {
            $vidString = self::idToString($k, $v);
            $vehicles[] = (string) $v;
        }

        $locations = [];
        foreach ($fleet->vehicles() as $k => $v) {
            $vidString = self::idToString($k, $v);
            $vehicleIdObj = $v instanceof VehicleId ? $v : VehicleId::fromString($vidString);
            $loc = $fleet->locationOf($vehicleIdObj);
            if ($loc !== null) {
                $locations[$vidString] = $loc->toArray();
            }
        }

        return new FleetDoctrine((string) $fleet->id(), array_values($vehicles), $locations);
    }

    public static function updateFromDomain(FleetDoctrine $entity, Fleet $fleet): FleetDoctrine
    {
        if (method_exists($entity, 'setVehicles') && method_exists($fleet, 'vehicles')) {
            $vehicles = [];
            foreach ($fleet->vehicles() as $k => $v) {
                $vidString = self::idToString($k, $v);
                $vehicles[] = (string) VehicleId::fromString($vidString);
            }
            $entity->setVehicles(array_values($vehicles));
        }

        if (method_exists($entity, 'setLocations') && method_exists($fleet, 'vehicles')) {
            $locations = [];
            foreach ($fleet->vehicles() as $k => $v) {
                $vidString = self::idToString($k, $v);
                $vehicleIdObj = $v instanceof VehicleId ? $v : VehicleId::fromString($vidString);
                $loc = $fleet->locationOf($vehicleIdObj);
                if ($loc !== null) {
                    $locations[$vidString] = $loc->toArray();
                }
            }
            $entity->setLocations($locations);
        }

        return $entity;
    }

    private static function idToString(mixed $key, mixed $value): string
    {
        if ($key instanceof VehicleId) {
            return (string) $key;
        }
        if (is_string($key) && $key !== '') {
            return $key;
        }

        if ($value instanceof VehicleId) {
            return (string) $value;
        }
        if (is_string($value) && $value !== '') {
            return $value;
        }

        if (is_array($value)) {
            if (isset($value['id']) && is_scalar($value['id']) && (string) $value['id'] !== '') {
                return (string) $value['id'];
            }
            if (count($value) === 1) {
                $first = reset($value);
                if (is_scalar($first) && (string) $first !== '') {
                    return (string) $first;
                }
            }
        }

        if (is_object($value)) {
            if (method_exists($value, '__toString')) {
                $s = (string) $value;
                if ($s !== '') {
                    return $s;
                }
            }
            if (method_exists($value, 'getId')) {
                $res = $value->getId();
                if ($res instanceof VehicleId) {
                    return (string) $res;
                }
                if (is_scalar($res) && (string) $res !== '') {
                    return (string) $res;
                }
            }
            if (property_exists($value, 'id') && is_scalar($value->id) && (string) $value->id !== '') {
                return (string) $value->id;
            }
        }

        throw new \InvalidArgumentException('Unable to extract the vehicle identifier (unexpected key/value pair).');
    }
}
