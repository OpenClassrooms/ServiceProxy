<?php

declare(strict_types=1);

use OpenClassrooms\ServiceProxy\Configuration;
use OpenClassrooms\ServiceProxy\ProxyFactory;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\inline_service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;
use OpenClassrooms\ServiceProxy\Handler\Handler\Event\SymfonyHttpEventHandler;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->bind('string $esbEndpoint', param('esb.endpoint'))
        ->bind('string $esbApiKey', param('esb.api_key'))
        ->autowire()
        ->autoconfigure()
    ;

    $services->load(
        'OpenClassrooms\\ServiceProxy\\Handler\\Handler\\',
        \dirname(__DIR__, 4) . '/src/Handler/Handler/*'
    );

    $services->load(
        'OpenClassrooms\\ServiceProxy\\Interceptor\\Interceptor\\',
        \dirname(__DIR__, 4) . '/src/Interceptor/Interceptor/*'
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
