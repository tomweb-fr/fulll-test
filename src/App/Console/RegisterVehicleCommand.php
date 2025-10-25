<?php

declare(strict_types=1);

namespace Fulll\App\Console;

use Fulll\App\Command\RegisterVehicle;
use Fulll\Domain\Exception\FleetNotFoundException;
use Fulll\Domain\Exception\VehicleAlreadyRegisteredException;
use Fulll\Domain\ValueObject\FleetId;
use Fulll\Domain\ValueObject\VehicleId;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'register-vehicle')]
final class RegisterVehicleCommand extends Command
{
    private MessageBusInterface $bus;

    public function __construct(MessageBusInterface $bus)
    {
        parent::__construct();
        $this->bus = $bus;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Register a vehicle to a fleet with vehicle plate number')
            ->addArgument('fleetId', InputArgument::REQUIRED, 'Fleet id')
            ->addArgument('vehiclePlateNumber', InputArgument::REQUIRED, 'Vehicle plate number');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fleetId = (string) $input->getArgument('fleetId');
        $vehicleId = (string) $input->getArgument('vehiclePlateNumber');

        $command = new RegisterVehicle(FleetId::fromString($fleetId), VehicleId::fromString($vehicleId));

        try {
            $this->bus->dispatch($command);
            $output->writeln(sprintf('Véhicule `%s` enregistré dans la flotte `%s`.', $vehicleId, $fleetId));
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            if ($e instanceof HandlerFailedException) {
                foreach ($e->getWrappedExceptions() as $nested) {
                    if ($nested instanceof VehicleAlreadyRegisteredException) {
                        $output->writeln(sprintf('<error>Le véhicule `%s` est déjà enregistré dans la flotte `%s`.</error>', $vehicleId, $fleetId));
                        return Command::FAILURE;
                    }

                    if ($nested instanceof FleetNotFoundException) {
                        $output->writeln(sprintf('<error>Flotte `%s` introuvable.</error>', $fleetId));
                        return Command::FAILURE;
                    }
                }

                $output->writeln('<error>Une erreur est survenue : %s</error>');
                return Command::FAILURE;
            }

            $output->writeln('<error>Une erreur est survenue lors de l\'enregistrement du véhicule.</error>');
            return Command::FAILURE;
        }
    }
}
