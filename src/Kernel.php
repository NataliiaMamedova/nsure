<?php

declare(strict_types=1);

namespace App;

use Bref\SymfonyBridge\BrefKernel;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class Kernel extends BrefKernel
{
    use MicroKernelTrait;

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->import('../config/{packages}/*.yaml');
        $container->import('../config/{packages}/' . $this->environment . '/*.yaml');

        if (is_file(\dirname(__DIR__) . '/config/services.yaml')) {
            $container->import('../config/{services}.yaml');
            $container->import('../config/{services}_' . $this->environment . '.yaml');
        } else {
            $path = \dirname(__DIR__) . '/config/services.php';
            if (is_file($path)) {
                (require $path)($container->withPath($path), $this);
            }
        }
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import('../config/{routes}/' . $this->environment . '/*.yaml');
        $routes->import('../config/{routes}/*.yaml');

        if (is_file(\dirname(__DIR__) . '/config/routes.yaml')) {
            $routes->import('../config/{routes}.yaml');
        } else {
            $path = \dirname(__DIR__) . '/config/routes.php';
            if (is_file($path)) {
                (require $path)($routes->withPath($path), $this);
            }
        }
    }
}
