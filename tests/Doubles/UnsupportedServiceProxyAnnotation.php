<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Doubles;

use OpenClassrooms\ServiceProxy\Annotations\ServiceProxyAnnotation;

/**
 * @Annotation
 */
class UnsupportedServiceProxyAnnotation implements ServiceProxyAnnotation
{
}
