<?php

declare(strict_types=1);

namespace Fulll\App\Console;

use Fulll\App\Command\CreateFleet;
use Fulll\App\Query\GetFleetQuery;
use Fulll\Domain\Fleet\Fleet;
use Fulll\Domain\ValueObject\FleetId;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'fleet:create')]
final class CreateFleetCommand extends Command
{
    private MessageBusInterface $bus;

    private const ERROR_MESSAGE = '<error>Un souci est survenu ou la flotte existe déjà</error>';

    public function __construct(MessageBusInterface $bus)
    {
        parent::__construct();
        $this->bus = $bus;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Create a fleet (prints fleetId)')
            ->addArgument('userId', InputArgument::OPTIONAL, 'Optional owner id (used as fleet id here)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $userId = $input->getArgument('userId');
        $fleetId = ($userId !== null && $userId !== '') ? (string) $userId : 'fleet-' . bin2hex(random_bytes(6));

        try {
            $envelopeCreate = $this->bus->dispatch(new CreateFleet(FleetId::fromString($fleetId)));
            $handledCreate = $envelopeCreate->last(HandledStamp::class);

            if (!$handledCreate instanceof HandledStamp) {
                $output->writeln(self::ERROR_MESSAGE);
                return Command::FAILURE;
            }

            $envelope = $this->bus->dispatch(new GetFleetQuery(FleetId::fromString($fleetId)));
            $handled = $envelope->last(HandledStamp::class);

            if (!$handled instanceof HandledStamp) {
                $output->writeln(self::ERROR_MESSAGE);
                return Command::FAILURE;
            }

            $result = $handled->getResult();

            if ($result instanceof Fleet) {
                $output->writeln((string) $result->id());
                return Command::SUCCESS;
            }

            $output->writeln(self::ERROR_MESSAGE);
            return Command::FAILURE;
        } catch (\Throwable $e) {
            $output->writeln(self::ERROR_MESSAGE);
            return Command::FAILURE;
        }
    }
}
