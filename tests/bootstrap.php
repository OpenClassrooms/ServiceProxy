<?php

namespace OpenClassrooms\ServiceProxy\Tests;

use Composer\Autoload\ClassLoader;
use Doctrine\Common\Annotations\AnnotationRegistry;

error_reporting(E_ALL | E_STRICT);

/* @var ClassLoader $loader */
$loader = require __DIR__.'/../vendor/autoload.php';
$loader->add('OpenClassrooms\ServiceProxy\Tests\\', __DIR__);

if (file_exists(__DIR__.'/../vendor/autoload.php')) {
    $loader = require_once __DIR__.'/../vendor/autoload.php';
    AnnotationRegistry::registerLoader('class_exists');

    return $loader;
}
