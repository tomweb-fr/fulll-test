<?php

declare(strict_types=1);

namespace Fulll\Domain\Exception;

final class VehicleNotRegisteredException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('vehicle-not-registered');
    }
}
