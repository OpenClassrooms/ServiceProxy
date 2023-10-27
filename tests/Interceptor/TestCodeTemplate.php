<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use OpenClassrooms\ServiceProxy\Configuration;
use OpenClassrooms\ServiceProxy\Interceptor\Config\CacheInterceptorConfig;
use OpenClassrooms\ServiceProxy\Interceptor\Impl\CacheInterceptor;
use OpenClassrooms\ServiceProxy\ProxyFactory;
use OpenClassrooms\ServiceProxy\Tests\CacheTestTrait;
use Symfony\Component\Filesystem\Filesystem;

if (empty($argv[1])) {
    throw new \InvalidArgumentException('You must provide a class name as argument');
}

$cacheDir = __DIR__ . '/../cache';

$fs = new Filesystem();
$fs->remove($cacheDir);

$config = new CacheInterceptorConfig();
$cacheHandlerMock = (new class() {
    use CacheTestTrait;
})->getCacheHandlerMock();
$cacheInterceptor = new CacheInterceptor($config, [$cacheHandlerMock]);

$proxyFactory = new ProxyFactory(
    new Configuration($cacheDir),
    [$cacheInterceptor],
    [$cacheInterceptor]
);

$classname = '\\OpenClassrooms\\ServiceProxy\\Tests\\tmp\\' . $argv[1];
$instance = new $classname();

$proxy = $proxyFactory->createProxy($instance);

$proxy->execute();

$cacheHit = !empty($cacheInterceptor->getHits());

exit($cacheHit ? 1 : 0);
