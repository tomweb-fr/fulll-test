<?php

namespace Tests\Domain\Fleet;

use Fulll\Domain\Exception\VehicleAlreadyParkedAtLocationException;
use Fulll\Domain\Exception\VehicleNotRegisteredException;
use Fulll\Domain\Fleet\Fleet;
use Fulll\Domain\ValueObject\FleetId;
use Fulll\Domain\ValueObject\Location;
use Fulll\Domain\ValueObject\VehicleId;
use PHPUnit\Framework\TestCase;

final class FleetTest extends TestCase
{
    public function test_register_and_has_vehicle(): void
    {
        $fleet = new Fleet(FleetId::fromString('f-1'));
        $vid = VehicleId::fromString('v-1');
        $this->assertFalse($fleet->hasVehicle($vid));
        $fleet->registerVehicle($vid);
        $this->assertTrue($fleet->hasVehicle($vid));
    }

    public function test_vehicles_returns_registered_list(): void
    {
        $fleet = new Fleet(FleetId::fromString('f-2'));
        $v1 = VehicleId::fromString('v-10');
        $v2 = VehicleId::fromString('v-11');
        $fleet->registerVehicle($v1);
        $fleet->registerVehicle($v2);
        $vehicles = $fleet->vehicles();
        $this->assertCount(2, $vehicles);
        $this->assertTrue($vehicles[0] instanceof VehicleId);
    }

    public function test_park_vehicle_requires_registered_vehicle(): void
    {
        $fleet = new Fleet(FleetId::fromString('f-3'));
        $vid = VehicleId::fromString('v-20');
        $location = new Location(48.8566, 2.3522);

        $this->expectException(VehicleNotRegisteredException::class);

        $fleet->parkVehicle($vid, $location);
    }

    public function test_park_vehicle_stores_location_for_registered_vehicle(): void
    {
        $fleet = new Fleet(FleetId::fromString('f-4'));
        $vid = VehicleId::fromString('v-21');
        $location = new Location(51.5074, -0.1278);

        $fleet->registerVehicle($vid);
        $fleet->parkVehicle($vid, $location);

        $stored = $fleet->locationOf($vid);
        $this->assertNotNull($stored);
        $this->assertTrue($stored->equals($location));
    }

    public function test_park_vehicle_throws_if_already_parked_at_same_location(): void
    {
        $fleet = new Fleet(FleetId::fromString('f-5'));
        $vid = VehicleId::fromString('v-22');
        $location = new Location(40.7128, -74.0060);

        $fleet->registerVehicle($vid);
        $fleet->parkVehicle($vid, $location);

        $this->expectException(VehicleAlreadyParkedAtLocationException::class);

        $fleet->parkVehicle($vid, $location);
    }
}
