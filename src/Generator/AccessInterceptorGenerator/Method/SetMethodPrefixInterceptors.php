<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Generator\AccessInterceptorGenerator\Method;

use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Generator\ParameterGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use ProxyManager\Generator\MethodGenerator;

/**
 * Implementation for {@see \ProxyManager\Proxy\AccessInterceptorInterface::setMethodPrefixInterceptor}
 * for access interceptor objects
 */
class SetMethodPrefixInterceptors extends MethodGenerator
{
    /**
     * Constructor
     *
     * @throws InvalidArgumentException
     */
    public function __construct(PropertyGenerator $prefixInterceptor)
    {
        parent::__construct('setPrefixInterceptors');

        $interceptors = new ParameterGenerator('prefixInterceptors');

        $interceptors->setType('array');
        $this->setParameter($interceptors);
        $this->setReturnType('void');
        $this->setBody('$this->' . $prefixInterceptor->getName() . ' = $prefixInterceptors;');
    }
}
