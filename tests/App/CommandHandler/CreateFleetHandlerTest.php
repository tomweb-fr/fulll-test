<?php

declare(strict_types=1);

namespace Fulll\Tests\App\CommandHandler;

use Fulll\App\Command\CreateFleet;
use Fulll\App\CommandHandler\CreateFleetHandler;
use Fulll\Domain\Fleet\Fleet;
use Fulll\Domain\Fleet\FleetRepositoryInterface;
use Fulll\Domain\ValueObject\FleetId;
use PHPUnit\Framework\TestCase;

final class CreateFleetHandlerTest extends TestCase
{
    public function test_it_creates_and_returns_fleet_id(): void
    {
        $fleetId = FleetId::fromString('fleet-123');

        $repo = $this->createMock(FleetRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('find')
            ->with($fleetId)
            ->willReturn(null);

        $repo->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Fleet $fleet) use ($fleetId) {
                return (string)$fleet->id() === (string)$fleetId;
            }));

        $handler = new CreateFleetHandler($repo);
        $result = $handler(new CreateFleet($fleetId));

        $this->assertSame((string)$fleetId, (string)$result);
    }

    public function test_it_throws_on_empty_fleet_id(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        FleetId::fromString('');
    }

    public function test_it_throws_when_fleet_already_exists(): void
    {
        $fleetId = FleetId::fromString('fleet-123');
        $existingFleet = new Fleet($fleetId);

        $repo = $this->createMock(FleetRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('find')
            ->with($fleetId)
            ->willReturn($existingFleet);

        $repo->expects($this->never())->method('save');

        $handler = new CreateFleetHandler($repo);

        $this->expectException(\RuntimeException::class);
        $handler(new CreateFleet($fleetId));
    }
}
