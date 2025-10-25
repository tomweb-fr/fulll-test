<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Hook\BeforeScenario;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use Fulll\App\Calculator;
use Fulll\App\Command\CreateFleet;
use Fulll\App\Command\ParkVehicle;
use Fulll\App\Command\RegisterVehicle;
use Fulll\App\CommandHandler\CreateFleetHandler;
use Fulll\App\CommandHandler\ParkVehicleHandler;
use Fulll\App\CommandHandler\RegisterVehicleHandler;
use Fulll\App\Query\GetFleetQuery;
use Fulll\App\QueryHandler\GetFleetQueryHandler;
use Fulll\Domain\Exception\VehicleAlreadyParkedAtLocationException;
use Fulll\Domain\Exception\VehicleAlreadyRegisteredException;
use Fulll\Domain\Exception\VehicleNotRegisteredException;
use Fulll\Domain\Fleet\Fleet;
use Fulll\Domain\ValueObject\FleetId;
use Fulll\Domain\ValueObject\Location;
use Fulll\Domain\ValueObject\VehicleId;
use Fulll\Infra\InMemory\FleetRepositoryInMemory;

class FeatureContext implements Context
{
    private const MY_FLEET = 'my-fleet';
    private const OTHER_FLEET = 'other-fleet';
    private array $fleet;
    private array $otherFleet;
    private string $vehicleId;
    private array $parkings;
    private ?array $location;
    private ?string $lastException;

    private ?FleetRepositoryInMemory $repo;
    private ?RegisterVehicleHandler $registerVehicleHandler;
    private ?ParkVehicleHandler $parkVehicleHandler;
    private ?CreateFleetHandler $createFleetHandler;
    private ?GetFleetQueryHandler $getFleetQueryHandler;
    private ?Fleet $fetchedFleet;

    public function __construct()
    {
        $this->fleet = [];
        $this->otherFleet = [];
        $this->parkings = [];
        $this->location = null;
        $this->vehicleId = '';
        $this->lastException = null;

        $this->repo = new FleetRepositoryInMemory();
        $this->registerVehicleHandler = new RegisterVehicleHandler($this->repo);
        $this->parkVehicleHandler = new ParkVehicleHandler($this->repo);
        $this->createFleetHandler = new CreateFleetHandler($this->repo);

        $this->getFleetQueryHandler = new GetFleetQueryHandler($this->repo);
        $this->fetchedFleet = null;
    }

    #[BeforeScenario]
    public function beforeScenario(): void
    {
        $this->fleet = [];
        $this->otherFleet = [];
        $this->parkings = [];
        $this->location = null;
        $this->vehicleId = '';
        $this->lastException = null;

        if ($this->repo instanceof FleetRepositoryInMemory) {
            $this->repo->clear();
        } else {
            $this->repo = new FleetRepositoryInMemory();
        }

        try {
            ($this->createFleetHandler)(new CreateFleet(FleetId::fromString(self::MY_FLEET)));
        } catch (\RuntimeException $e) {}

        try {
            ($this->createFleetHandler)(new CreateFleet(FleetId::fromString(self::OTHER_FLEET)));
        } catch (\RuntimeException $e) {}

        $this->registerVehicleHandler = new RegisterVehicleHandler($this->repo);
        $this->parkVehicleHandler = new ParkVehicleHandler($this->repo);

        $this->getFleetQueryHandler = new GetFleetQueryHandler($this->repo);
        $this->fetchedFleet = null;
    }

    #[When('I multiply :a by :b into :var')]
    public function iMultiply(int $a, int $b, string $var): void
    {
        $calculator = new Calculator();
        $this->$var = $calculator->multiply($a, $b);
    }

    #[Then(':var should be equal to :value')]
    public function aShouldBeEqualTo(string $var, int $value): void
    {
        if ($value !== $this->$var) {
            throw new \RuntimeException(sprintf('%s is expected to be equal to %s, got %s', $var, $value, $this->$var));
        }
    }

    #[Given('my fleet')]
    public function myFleet(): void
    {
        $this->fleet = [];
        $this->otherFleet = [];
        $this->parkings = [];
        $this->lastException = null;
    }

    #[Given('a vehicle')]
    public function aVehicle(): void
    {
        $this->vehicleId = 'v-' . uniqid();
    }

    #[Given('a vehicle with id :id')]
    public function aVehicleWithId(string $id): void
    {
        $this->vehicleId = $id;
    }

    /**
     * @throws Exception
     */
    #[Given('I have registered this vehicle into my fleet')]
    public function iHaveRegisteredThisVehicleIntoMyFleet(): void
    {
        if ($this->registerVehicleHandler !== null) {
            ($this->registerVehicleHandler)(new RegisterVehicle(
                FleetId::fromString(self::MY_FLEET),
                VehicleId::fromString($this->vehicleId)
            ));
            $this->fleet[$this->vehicleId] = true;
            return;
        }

        $this->registerVehicleIntoFleet($this->fleet);
    }

    #[Given('the fleet of another user')]
    public function theFleetOfAnotherUser(): void
    {
        $this->otherFleet = [];
    }

    /**
     * @throws Exception
     */
    #[Given('this vehicle has been registered into the other user\'s fleet')]
    public function thisVehicleHasBeenRegisteredIntoTheOtherUsersFleet(): void
    {
        if ($this->registerVehicleHandler !== null) {
            ($this->registerVehicleHandler)(new RegisterVehicle(
                FleetId::fromString(self::OTHER_FLEET),
                VehicleId::fromString($this->vehicleId)
            ));
            $this->otherFleet[$this->vehicleId] = true;
            return;
        }

        $this->registerVehicleIntoFleet($this->otherFleet);
    }

    #[When('I register this vehicle into my fleet')]
    public function iRegisterThisVehicleIntoMyFleet(): void
    {
        try {
            if ($this->registerVehicleHandler !== null) {
                ($this->registerVehicleHandler)(new RegisterVehicle(
                    FleetId::fromString(self::MY_FLEET),
                    VehicleId::fromString($this->vehicleId)
                ));
                $this->fleet[$this->vehicleId] = true;
            } else {
                $this->registerVehicleIntoFleet($this->fleet);
            }
        } catch (\Exception $e) {
            if ($e instanceof VehicleAlreadyRegisteredException || str_contains($e->getMessage(), 'already registered')) {
                $this->lastException = 'vehicle-already-registered';
                return;
            }

            $this->lastException = $e->getMessage();
        }
    }

    #[When('I try to register this vehicle into my fleet')]
    public function iTryToRegisterThisVehicleIntoMyFleet(): void
    {
        $this->iRegisterThisVehicleIntoMyFleet();
    }

    /**
     * @throws Exception
     */
    #[Then('this vehicle should be part of my vehicle fleet')]
    public function thisVehicleShouldBePartOfMyVehicleFleet(): void
    {
        if (!isset($this->fleet[$this->vehicleId])) {
            throw new \Exception('vehicle-not-in-fleet');
        }
    }

    /**
     * @throws Exception
     */
    #[Then('I should be informed if this vehicle has already been registered into my fleet')]
    public function iShouldBeInformedThisVehicleIsAlreadyRegistered(): void
    {
        if ($this->lastException !== 'vehicle-already-registered') {
            throw new \Exception('expected vehicle-already-registered, got: ' . var_export($this->lastException, true));
        }
    }

    #[Given('a location')]
    public function aLocation(): void
    {
        $this->location = ['lat' => 33.8566, 'lon' => 4.3522];
    }

    /**
     * @throws Exception
     */
    #[Given('my vehicle has been parked into this location')]
    public function myVehicleHasBeenParkedIntoThisLocation(): void
    {
        $this->parkVehicleAtLocation();
    }

    #[When('I park my vehicle at this location')]
    public function iParkMyVehicleAtThisLocation(): void
    {
        try {
            $this->parkVehicleAtLocation();
        } catch (VehicleNotRegisteredException $e) {
            $this->lastException = 'vehicle-not-registered';
        } catch (VehicleAlreadyParkedAtLocationException $e) {
            $this->lastException = 'vehicle-already-parked-at-location';
        } catch (\Exception $e) {
            $this->lastException = $e->getMessage();
        }
    }

    #[When('I try to park my vehicle at this location')]
    public function iTryToParkMyVehicleAtThisLocation(): void
    {
        $this->iParkMyVehicleAtThisLocation();
    }

    /**
     * @throws Exception
     */
    #[Then('the known location of my vehicle should verify this location')]
    public function theKnownLocationOfMyVehicleShouldVerifyThisLocation(): void
    {
        if (!isset($this->parkings[$this->vehicleId])) {
            throw new \Exception('vehicle-not-parked');
        }
        if ($this->parkings[$this->vehicleId] !== $this->location) {
            throw new \Exception('location-mismatch');
        }
    }

    /**
     * @throws Exception
     */
    #[Then('I should be informed that my vehicle is already parked at this location')]
    public function iShouldBeInformedThatMyVehicleIsAlreadyParkedAtThisLocation(): void
    {
        if ($this->lastException !== 'vehicle-already-parked-at-location') {
            throw new \Exception('expected vehicle-already-parked-at-location, got: ' . var_export($this->lastException, true));
        }
    }

    #[When('I fetch fleet :id')]
    public function iFetchFleet(string $id): void
    {
        $query = GetFleetQuery::fromString($id);
        try {
            $this->fetchedFleet = ($this->getFleetQueryHandler)($query);
        } catch (\Exception $e) {
            $this->lastException = $e->getMessage();
            $this->fetchedFleet = null;
        }
    }

    /**
     * @throws Exception
     */
    #[Then('the fetched fleet should be returned')]
    public function theFetchedFleetShouldBeReturned(): void
    {
        if ($this->fetchedFleet === null) {
            throw new \Exception('no-fleet-fetched');
        }
        if (!$this->fetchedFleet instanceof Fleet) {
            throw new \Exception('fetched-not-a-fleet');
        }
    }

    /**
     * @throws Exception
     */
    private function registerVehicleIntoFleet(array &$fleet): void
    {
        if (isset($fleet[$this->vehicleId])) {
            throw new \Exception('vehicle-already-registered');
        }

        $fleet[$this->vehicleId] = true;
    }

    /**
     * @throws Exception
     */
    private function parkVehicleAtLocation(): void
    {
        if ($this->parkVehicleHandler !== null) {
            if ($this->location === null) {
                throw new \Exception('no-location-defined');
            }

            $command = new ParkVehicle(
                FleetId::fromString(self::MY_FLEET),
                VehicleId::fromString($this->vehicleId),
                new Location($this->location['lat'], $this->location['lon'])
            );

            try {
                ($this->parkVehicleHandler)($command);
                $this->parkings[$this->vehicleId] = $this->location;
                return;
            } catch (VehicleNotRegisteredException $e) {
                $this->lastException = 'vehicle-not-registered';
                throw $e;
            } catch (VehicleAlreadyParkedAtLocationException $e) {
                $this->lastException = 'vehicle-already-parked-at-location';
                throw $e;
            }
        }

        if (!$this->isVehicleRegisteredAnywhere()) {
            throw new \Exception('vehicle-not-registered');
        }
        $current = $this->parkings[$this->vehicleId] ?? null;
        if ($current !== null && $current === $this->location) {
            throw new \Exception('vehicle-already-parked-at-location');
        }
        $this->parkings[$this->vehicleId] = $this->location;
    }

    private function isVehicleRegisteredAnywhere(): bool
    {
        return isset($this->fleet[$this->vehicleId]) || isset($this->otherFleet[$this->vehicleId]);
    }
}
