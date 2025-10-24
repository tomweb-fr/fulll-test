<?php

declare(strict_types=1);

namespace Tests\Handler;

use PHPUnit\Framework\TestCase;
use Fulll\Infra\InMemory\FleetRepositoryInMemory;
use Fulll\App\Handler\RegisterVehicleHandler;
use Fulll\App\Command\RegisterVehicle;
use Fulll\Domain\Exception\VehicleAlreadyRegisteredException;
use Fulll\Domain\Fleet\FleetId;
use Fulll\Domain\Vehicle\VehicleId;

final class RegisterVehicleHandlerTest extends TestCase
{
    private RegisterVehicleHandler $handler;

    protected function setUp(): void
    {
        $repo = new FleetRepositoryInMemory();
        $this->handler = new RegisterVehicleHandler($repo);
    }

    public function test_register_vehicle_success(): void
    {
        ($this->handler)(new RegisterVehicle(
            FleetId::fromString('my-fleet'),
            VehicleId::fromString('v-123')
        ));
        $this->addToAssertionCount(1);
    }

    public function test_registering_same_vehicle_twice_throws(): void
    {
        ($this->handler)(new RegisterVehicle(
            FleetId::fromString('my-fleet'),
            VehicleId::fromString('v-456')
        ));

        $this->expectException(VehicleAlreadyRegisteredException::class);
        ($this->handler)(new RegisterVehicle(
            FleetId::fromString('my-fleet'),
            VehicleId::fromString('v-456')
        ));
    }
}
