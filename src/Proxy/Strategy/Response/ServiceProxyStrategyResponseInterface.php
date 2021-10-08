<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Proxy\Strategy\Response;

interface ServiceProxyStrategyResponseInterface
{
    public function getPreSource(): string;

    public function getPostSource(): string;

    public function getExceptionSource(): string;

    /**
     * @return \Laminas\Code\Generator\PropertyGenerator[]
     */
    public function getProperties(): array;

    /**
     * @return \Laminas\Code\Generator\MethodGenerator[]
     */
    public function getMethods(): array;
}
