<?php

declare(strict_types=1);

namespace Fulll\Infra\Doctrine;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Fulll\Domain\Fleet\Fleet;
use Fulll\Domain\Repository\FleetRepositoryInterface;
use Fulll\Domain\ValueObject\FleetId;
use Fulll\Infra\Doctrine\Entity\FleetDoctrine;
use Fulll\Infra\Doctrine\Mapper\FleetDoctrineMapper;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

final readonly class FleetRepositoryDoctrine implements FleetRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private string                 $env
    ) {}

    public function find(FleetId|string $id): ?Fleet
    {
        $key = $id instanceof FleetId ? (string) $id : (string) $id;
        $entity = $this->em->getRepository(FleetDoctrine::class)->find($key);
        if ($entity === null) {
            return null;
        }

        return FleetDoctrineMapper::toDomain($entity);
    }

    /**
     * @throws \Throwable
     * @throws Exception
     */
    public function save(Fleet $fleet): void
    {
        $key = (string) $fleet->id();

        $this->em->getConnection()->beginTransaction();
        try {
            $existing = $this->em->getRepository(FleetDoctrine::class)->find($key);
            $entity = FleetDoctrineMapper::toEntity($fleet);

            if ($existing === null) {
                $this->em->persist($entity);
            } else {
                FleetDoctrineMapper::updateFromDomain($existing, $fleet);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (UniqueConstraintViolationException $e) {
            if ($this->em->getConnection()->isTransactionActive()) {
                $this->em->getConnection()->rollBack();
            }
            throw new \RuntimeException('fleet-already-exists');
        } catch (\Throwable $e) {
            if ($this->em->getConnection()->isTransactionActive()) {
                $this->em->getConnection()->rollBack();
            }
            throw $e;
        }
    }

    public function clear(): void
    {
        if ($this->env !== 'dev') {
            throw new \RuntimeException('clear() allowed only in the dev environment.');
        }

        $platform = $this->em->getConnection()->getDatabasePlatform();
        $this->em->getConnection()->executeStatement(
            $platform->getTruncateTableSQL('fleet', true)
        );
        $this->em->clear();
    }
}
