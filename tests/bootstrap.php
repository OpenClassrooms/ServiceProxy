<?php

namespace OpenClassrooms\ServiceProxy\Tests;

use Composer\Autoload\ClassLoader;
use Doctrine\Common\Annotations\AnnotationRegistry;

error_reporting(E_ALL | E_STRICT);

/* @var ClassLoader $loader */
if (file_exists(__DIR__.'/../vendor/autoload.php')) {
    $loader = require_once __DIR__.'/../vendor/autoload.php';
    AnnotationRegistry::registerLoader('class_exists');

    return $loader;
}
