# fulll-test
[WIP] In progress
Require : composer, php 8,4, mysql

Installation : 
composer install

Tests:
vendor/behat/behat/bin/behat
./vendor/bin/phpunit

Usage :
- php bin/console create < ? my-fleet>
- php bin/console register-vehicle <fleetId> <vehiclePlateNumber>
- php bin/console localize-vehicle <fleetId> <vehiclePlateNumber> <lat> <lng>
- php bin/console dump-fleet-data <fleetId>

Improvesments to be done :
- Refactoring code
- Adapt Behat tests -> FeatureContext with Doctrine Test environment
- php stan / cs fix corrections :
composer analyse
composer cs-check
composer cs-fix
- Improve Doctrine Entities with relations : Vehicle Entity, Location Entity
- Add locking mechanism 
- Add cache
- Add logs
- Check security issues 

