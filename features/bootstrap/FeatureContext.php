<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Fulll\App\Calculator;
use Behat\Step\Given;
use Behat\Step\When;
use Behat\Step\Then;

class FeatureContext implements Context
{
    private array $fleet;
    private array $otherFleet;
    private string $vehicleId;
    private array $parkings;
    private ?array $location;
    private ?string $lastException;

    public function __construct()
    {
        $this->fleet = [];
        $this->otherFleet = [];
        $this->parkings = [];
        $this->location = null;
        $this->vehicleId = '';
        $this->lastException = null;
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
        $this->registerVehicleIntoFleet($this->otherFleet);
    }

    #[When('I register this vehicle into my fleet')]
    public function iRegisterThisVehicleIntoMyFleet(): void
    {
        try {
            $this->registerVehicleIntoFleet($this->fleet);
        } catch (\Exception $e) {
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
    #[Then('I should be informed this this vehicle has already been registered into my fleet')]
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
