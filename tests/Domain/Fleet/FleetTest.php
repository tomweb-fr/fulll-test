<?php

namespace Tests\Domain\Fleet;

use PHPUnit\Framework\TestCase;
use Fulll\Domain\Fleet\Fleet;
use Fulll\Domain\Fleet\FleetId;
use Fulll\Domain\Vehicle\VehicleId;

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
}
