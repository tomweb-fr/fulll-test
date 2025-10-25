<?php

declare(strict_types=1);

namespace Fulll\Infra\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Fulll\Domain\Fleet\Fleet;
use Fulll\Domain\Repository\FleetRepositoryInterface;
use Fulll\Domain\ValueObject\FleetId;
use Fulll\Domain\ValueObject\VehicleId;
use Fulll\Domain\ValueObject\Location;
use Fulll\Infra\Doctrine\Entity\FleetDoctrine;

final class FleetRepositoryDoctrine implements FleetRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em) {}

    public function find(FleetId|string $id): ?Fleet
    {
        $key = $id instanceof FleetId ? (string) $id : (string) $id;
        $entity = $this->em->getRepository(FleetDoctrine::class)->find($key);
        if ($entity === null) {
            return null;
        }

        $fleetId = FleetId::fromString($entity->getId());

        $vehicles = [];
        foreach ($entity->getVehicles() as $v) {
            $vidString = (string) $v;
            $vehicles[$vidString] = VehicleId::fromString($vidString);
        }

        $locations = [];
        foreach ($entity->getLocations() as $veh => $loc) {
            $vidString = (string) $veh;
            $locations[$vidString] = Location::fromString((string) $loc);
        }

        return new Fleet($fleetId, $vehicles, $locations);
    }

    public function save(Fleet $fleet): void
    {
        $key = (string) $fleet->id();

        $this->em->getConnection()->beginTransaction();
        try {
            $existing = $this->em->getRepository(FleetDoctrine::class)->find($key);
            if ($existing !== null) {
                $this->em->getConnection()->rollBack();
                throw new \RuntimeException('fleet-already-exists');
            }

            $vehicles = [];
            foreach ($fleet->vehicles() as $v) {
                $vehicles[] = (string) $v;
            }

            $locations = [];
            foreach ($fleet->vehicles() as $v) {
                $vid = (string) $v;
                $loc = $fleet->locationOf($v);
                if ($loc !== null) {
                    $locations[$vid] = (string) $loc;
                }
            }

            $entity = new FleetDoctrine($key, $vehicles, $locations);
            $this->em->persist($entity);
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Throwable $e) {
            if ($this->em->getConnection()->isTransactionActive()) {
                $this->em->getConnection()->rollBack();
            }
            throw $e;
        }
    }

    public function clear(): void
    {
    }
}
