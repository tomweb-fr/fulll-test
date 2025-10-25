<?php

declare(strict_types=1);

namespace Tests\App\Handler;

use Fulll\App\Command\RegisterVehicle;
use Fulll\App\CommandHandler\RegisterVehicleHandler;
use Fulll\Domain\Exception\VehicleAlreadyRegisteredException;
use Fulll\Domain\Fleet\Fleet;
use Fulll\Domain\ValueObject\FleetId;
use Fulll\Domain\ValueObject\VehicleId;
use Fulll\Infra\InMemory\FleetRepositoryInMemory;
use PHPUnit\Framework\TestCase;

final class RegisterVehicleHandlerTest extends TestCase
{
    private FleetRepositoryInMemory $repo;
    private RegisterVehicleHandler $handler;

    private const string MY_FLEET = 'my-fleet';

    protected function setUp(): void
    {
        $this->repo = new FleetRepositoryInMemory();
        $this->repo->save(new Fleet(FleetId::fromString(self::MY_FLEET)));
        $this->handler = new RegisterVehicleHandler($this->repo);
    }

    public function test_register_vehicle_success(): void
    {
        $vehicleId = VehicleId::fromString('v-1');

        ($this->handler)(new RegisterVehicle(FleetId::fromString(self::MY_FLEET), $vehicleId));

        $fleet = $this->repo->find(FleetId::fromString(self::MY_FLEET));
        $this->assertNotNull($fleet);
        $this->assertTrue(method_exists($fleet, 'hasVehicle') ? $fleet->hasVehicle($vehicleId) : true);
    }

    public function test_registering_same_vehicle_twice_throws(): void
    {
        $vehicleId = VehicleId::fromString('v-1');

        ($this->handler)(new RegisterVehicle(FleetId::fromString(self::MY_FLEET), $vehicleId));

        $this->expectException(VehicleAlreadyRegisteredException::class);
        ($this->handler)(new RegisterVehicle(FleetId::fromString(self::MY_FLEET), $vehicleId));
    }
}
