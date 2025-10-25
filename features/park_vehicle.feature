Feature: Park a vehicle

  In order to not forget where I've parked my vehicle
  As an application user
  I should be able to indicate my vehicle location

  Background:
    Given my fleet
    When I fetch fleet "my-fleet"
    And a vehicle with id "v-park-1"
    And I have registered this vehicle into my fleet

  Scenario: Successfully park a vehicle
    And a location
    When I park my vehicle at this location
    Then the known location of my vehicle should verify this location

  Scenario: Can't localize my vehicle to the same location two times in a row
    And a location
    And my vehicle has been parked into this location
    When I try to park my vehicle at this location
    Then I should be informed that my vehicle is already parked at this location
