<?php


declare(strict_types=1);

namespace Fulll\Infra\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'fleet')]
final class FleetDoctrine
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 128)]
    private string $id;

    /** @var array<string> */
    #[ORM\Column(type: 'json')]
    private array $vehicles = [];

    /** @var array<string, string> */
    #[ORM\Column(type: 'json')]
    private array $locations = [];

    public function __construct(string $id, array $vehicles = [], array $locations = [])
    {
        $this->id = $id;
        $this->vehicles = $vehicles;
        $this->locations = $locations;
    }

    public function getId(): string
    {
        return $this->id;
    }

    /** @return string[] */
    public function getVehicles(): array
    {
        return $this->vehicles;
    }

    /** @return array<string, string> */
    public function getLocations(): array
    {
        return $this->locations;
    }

    /** @param string[] $vehicles */
    public function setVehicles(array $vehicles): void
    {
        $this->vehicles = $vehicles;
    }

    /** @param array<string,string> $locations */
    public function setLocations(array $locations): void
    {
        $this->locations = $locations;
    }
}
