<?php

declare(strict_types=1);

namespace Fulll;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

final class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $confDir = $this->getProjectDir() . '/config';

        $container->import($confDir . '/{packages}/*.yaml');
        $container->import($confDir . '/{packages}/' . $this->environment . '/*.yaml');

        $container->import($confDir . '/{services}.yaml');
        $container->import($confDir . '/{services}_' . $this->environment . '.yaml', null);
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
    }
}
