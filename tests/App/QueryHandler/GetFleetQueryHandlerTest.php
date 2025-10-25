<?php

declare(strict_types=1);

namespace Fulll\Tests\App\QueryHandler;

use Fulll\App\Query\GetFleetQuery;
use Fulll\App\QueryHandler\GetFleetQueryHandler;
use Fulll\Domain\Fleet\Fleet;
use Fulll\Domain\Repository\FleetRepositoryInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use \Fulll\Domain\ValueObject\FleetId;

final class GetFleetQueryHandlerTest extends TestCase
{
    /**
     * @throws Exception
     * @throws \ReflectionException
     */
    public function testHandleReturnsFleet(): void
    {
        $fleetRef = new \ReflectionClass(Fleet::class);
        $fleet = $fleetRef->newInstanceWithoutConstructor();

        $fleetId = FleetId::fromString('fleet-123');
        $idProp = $fleetRef->getProperty('id');
        $idProp->setValue($fleet, $fleetId);

        $repo = $this->createMock(FleetRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('find')
            ->with($this->callback(function ($id) {
                return $id instanceof FleetId && (string) $id === 'fleet-123';
            }))
            ->willReturn($fleet);

        $handler = new GetFleetQueryHandler($repo);
        $query = GetFleetQuery::fromString('fleet-123');

        $result = $handler->handle($query);

        $this->assertSame($fleet, $result);

        $this->assertSame('fleet-123', (string) $idProp->getValue($result));
    }

    /**
     * @throws Exception
     */
    public function testHandleThrowsOnInvalidQuery(): void
    {
        $repo = $this->createMock(FleetRepositoryInterface::class);
        $handler = new GetFleetQueryHandler($repo);

        $this->expectException(\InvalidArgumentException::class);

        $handler->handle(new \stdClass());
    }
}
