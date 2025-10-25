<?php

declare(strict_types=1);

namespace Fulll\App\Console;

use Fulll\App\Command\ParkVehicle;
use Fulll\Domain\ValueObject\FleetId;
use Fulll\Domain\ValueObject\VehicleId;
use Fulll\Domain\ValueObject\Location;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'localize-vehicle')]
final class LocalizeVehicleCommand extends Command
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
            ->setDescription('Localize (park) a vehicle with latitude and longitude')
            ->addArgument('fleetId', InputArgument::REQUIRED, 'Fleet id')
            ->addArgument('vehiclePlateNumber', InputArgument::REQUIRED, 'Vehicle plate number')
            ->addArgument('lat', InputArgument::REQUIRED, 'Latitude')
            ->addArgument('lng', InputArgument::REQUIRED, 'Longitude');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fleetId = (string)$input->getArgument('fleetId');
        $vehicleId = (string)$input->getArgument('vehiclePlateNumber');
        $lat = (float)$input->getArgument('lat');
        $lng = (float)$input->getArgument('lng');

        $command = new ParkVehicle(
            FleetId::fromString($fleetId),
            VehicleId::fromString($vehicleId),
            new Location($lat, $lng)
        );

        try {
            $this->bus->dispatch($command);
            $output->writeln(sprintf('Véhicule `%s` localisé à [%s, %s] dans la flotte `%s`.', $vehicleId, $lat, $lng, $fleetId));
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            if ($e instanceof HandlerFailedException) {
                foreach ($e->getWrappedExceptions() as $nested) {
                    $output->writeln(sprintf('<error>%s</error>', $nested->getMessage()));
                    return Command::FAILURE;
                }
            }

            $output->writeln('<error>Une erreur est survenue lors de la localisation du véhicule.</error>');
            return Command::FAILURE;
        }
    }
}
