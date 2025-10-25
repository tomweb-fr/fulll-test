<?php

declare(strict_types=1);

namespace Fulll\App\Console;

use Fulll\Domain\Repository\FleetRepositoryInterface;
use Fulll\Domain\ValueObject\FleetId;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'dump-fleet-data')]
final class DumpFleetDataCommand extends Command
{
    public function __construct(private FleetRepositoryInterface $repo)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Dump fleet data from persistence for a single fleet (fleetId required)')
            ->addArgument('fleetId', InputArgument::REQUIRED, 'Fleet id');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $fleetIdArg = $input->getArgument('fleetId');

        $fleetId = FleetId::fromString((string) $fleetIdArg);
        $fleet = $this->repo->find($fleetId);

        if ($fleet === null) {
            $io->error(sprintf('Fleet `%s` introuvable.', (string) $fleetIdArg));
            return Command::FAILURE;
        }

        $normalized = $this->normalize($fleet);
        $tidy = $this->tidyStructure($normalized);
        $json = $this->toPrettyJson($tidy);

        $io->writeln($json);
        return Command::SUCCESS;
    }

    private function normalize(mixed $data): mixed
    {
        if (is_array($data)) {
            $out = [];
            foreach ($data as $k => $v) {
                $out[$k] = $this->normalize($v);
            }
            return $out;
        }

        if (is_object($data)) {
            if (method_exists($data, 'toArray')) {
                return $this->normalize($data->toArray());
            }

            if ($data instanceof \JsonSerializable) {
                return $this->normalize($data->jsonSerialize());
            }

            $vars = get_object_vars($data);
            if (!empty($vars)) {
                return $this->normalize($vars);
            }

            $result = [];
            $ref = new \ReflectionObject($data);
            foreach ($ref->getProperties() as $prop) {
                $result[$prop->getName()] = $this->normalize($prop->getValue($data));
            }
            return $result;
        }

        return $data;
    }

    private function tidyStructure(mixed $data): mixed
    {
        if (!is_array($data)) {
            return $data;
        }

        if (array_key_exists('id', $data) && is_array($data['id']) && array_key_exists('id', $data['id']) && count($data['id']) === 1) {
            $data['id'] = $data['id']['id'];
        }

        if (array_key_exists('vehicles', $data) && is_array($data['vehicles'])) {
            $vehicles = $data['vehicles'];

            $allSimple = true;
            foreach ($vehicles as $v) {
                if (!is_array($v) || count($v) !== 1 || !array_key_exists('id', $v)) {
                    $allSimple = false;
                    break;
                }
            }

            if ($allSimple) {
                $list = [];
                foreach ($vehicles as $v) {
                    $list[] = $v['id'];
                }
                $data['vehicles'] = $list;
            } else {
                $list = [];
                foreach ($vehicles as $key => $val) {
                    if (is_array($val)) {
                        if (!array_key_exists('id', $val)) {
                            $val['id'] = (string)$key;
                        }
                        $list[] = $val;
                    } else {
                        $list[] = ['id' => (string)$key, 'value' => $val];
                    }
                }
                $data['vehicles'] = $list;
            }
        }

        if (array_key_exists('locations', $data) && is_array($data['locations'])) {
            $locs = $data['locations'];
            $newLocs = [];
            foreach ($locs as $key => $val) {
                if (!is_array($val)) {
                    $newLocs[$key] = $val;
                    continue;
                }

                if (!array_key_exists('lon', $val) && array_key_exists('lng', $val)) {
                    $val['lon'] = $val['lng'];
                    unset($val['lng']);
                }

                if (array_key_exists('lat', $val)) {
                    $val['lat'] = is_numeric($val['lat']) ? (float)$val['lat'] : $val['lat'];
                }
                if (array_key_exists('lon', $val)) {
                    $val['lon'] = is_numeric($val['lon']) ? (float)$val['lon'] : $val['lon'];
                }

                $newLocs[$key] = $val;
            }
            $data['locations'] = $newLocs;
        }

        foreach ($data as $k => $v) {
            $data[$k] = $this->tidyStructure($v);
        }

        return $data;
    }

    private function toPrettyJson(mixed $data): string
    {
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            return print_r($data, true);
        }
        return $json;
    }
}
