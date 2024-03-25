<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Generator\AccessInterceptorGenerator\Method;

use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Generator\ParameterGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use ProxyManager\Generator\MethodGenerator;

/**
 * Implementation for {@see \ProxyManager\Proxy\AccessInterceptorInterface::setMethodSuffixInterceptor}
 * for access interceptor objects
 */
class SetMethodSuffixInterceptors extends MethodGenerator
{
    /**
     * Constructor
     *
     * @throws InvalidArgumentException
     */
    public function __construct(PropertyGenerator $suffixInterceptor)
    {
        parent::__construct('setSuffixInterceptors');

        $interceptors = new ParameterGenerator('suffixInterceptors');

        $interceptors->setType('array');
        $this->setParameter($interceptors);
        $this->setReturnType('void');
        $this->setBody('$this->' . $suffixInterceptor->getName() . ' = $suffixInterceptors;');
    }
}
