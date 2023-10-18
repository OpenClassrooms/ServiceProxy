<?php

declare(strict_types=1);

use OpenClassrooms\ServiceProxy\Configuration;
use OpenClassrooms\ServiceProxy\ProxyFactory;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\inline_service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure()
    ;

    $services->load(
        'OpenClassrooms\\ServiceProxy\\Handler\\Impl\\',
        \dirname(__DIR__, 4) . '/src/Handler/Impl/*'
    );

    $services->load(
        'OpenClassrooms\\ServiceProxy\\Interceptor\\Impl\\',
        \dirname(__DIR__, 4) . '/src/Interceptor/Impl/*'
    );

    $services->set(ProxyFactory::class)
        ->public()
        ->args([

            inline_service(Configuration::class)
                ->args([
                    '$proxiesDir' => param('openclassrooms.service_proxy.cache_dir'),
                    '$eval' => param('openclassrooms.service_proxy.eval'),
                ]),
            tagged_iterator('openclassrooms.service_proxy.prefix_interceptor'),
            tagged_iterator('openclassrooms.service_proxy.suffix_interceptor'),
        ])
    ;
};
