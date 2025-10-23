<?php

declare(strict_types=1);

namespace Tests\Handler;

use PHPUnit\Framework\TestCase;
use Fulll\Infra\InMemory\FleetRepositoryInMemory;
use Fulll\App\Handler\RegisterVehicleHandler;
use Fulll\App\Command\RegisterVehicle;
use Fulll\Domain\Exception\VehicleAlreadyRegisteredException;

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
        ($this->handler)(new RegisterVehicle('my-fleet', 'v-123'));
        $this->addToAssertionCount(1);
    }

    public function test_registering_same_vehicle_twice_throws(): void
    {
        ($this->handler)(new RegisterVehicle('my-fleet', 'v-456'));

        $this->expectException(VehicleAlreadyRegisteredException::class);
        ($this->handler)(new RegisterVehicle('my-fleet', 'v-456'));
    }
}
