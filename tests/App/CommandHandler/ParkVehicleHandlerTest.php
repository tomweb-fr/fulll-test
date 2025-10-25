<?php

declare(strict_types=1);

namespace Tests\App\Handler;

use Fulll\App\Command\ParkVehicle;
use Fulll\App\CommandHandler\ParkVehicleHandler;
use Fulll\Domain\Exception\VehicleAlreadyParkedAtLocationException;
use Fulll\Domain\Fleet\Fleet;
use Fulll\Domain\Repository\FleetRepositoryInterface;
use Fulll\Domain\ValueObject\FleetId;
use Fulll\Domain\ValueObject\Location;
use Fulll\Domain\ValueObject\VehicleId;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

final class ParkVehicleHandlerTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testInvokeParksVehicleOnExistingFleet(): void
    {
        $repo = $this->createMock(FleetRepositoryInterface::class);

        $vehicleId = VehicleId::fromString('v-2');
        $location = new Location(51.5074, -0.1278);

        $existingFleet = new Fleet(FleetId::fromString('fleet-2'));
        $existingFleet->registerVehicle($vehicleId);

        $repo->expects($this->once())
            ->method('find')
            ->willReturn($existingFleet);

        $repo->expects($this->once())
            ->method('save')
            ->with($this->identicalTo($existingFleet));

        $handler = new ParkVehicleHandler($repo);

        $command = new ParkVehicle(
            FleetId::fromString('fleet-2'),
            VehicleId::fromString('v-2'),
            $location
        );

        $handler($command);

        $stored = $existingFleet->locationOf($vehicleId);
        $this->assertNotNull($stored);
        $this->assertTrue($stored->equals($location));
    }

    /**
     * @throws Exception
     */
    public function testInvokePropagatesDuplicateParkingException(): void
    {
        $repo = $this->createMock(FleetRepositoryInterface::class);

        $vehicleId = VehicleId::fromString('v-3');
        $location = new Location(40.7128, -74.0060);

        $existingFleet = new Fleet(FleetId::fromString('fleet-3'));
        $existingFleet->registerVehicle($vehicleId);
        $existingFleet->parkVehicle($vehicleId, $location);

        $repo->expects($this->once())
            ->method('find')
            ->willReturn($existingFleet);

        $repo->expects($this->never())
            ->method('save');

        $handler = new ParkVehicleHandler($repo);

        $command = new ParkVehicle(
            FleetId::fromString('fleet-3'),
            VehicleId::fromString('v-3'),
            $location
        );

        $this->expectException(VehicleAlreadyParkedAtLocationException::class);

        $handler($command);
    }
}
