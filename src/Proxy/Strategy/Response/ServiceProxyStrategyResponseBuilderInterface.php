<?php

namespace OpenClassrooms\ServiceProxy\Proxy\Strategy\Response;

/**
 * @author Romain Kuzniak <romain.kuzniak@openclassrooms.com>
 */
interface ServiceProxyStrategyResponseBuilderInterface
{
    /**
     * @return ServiceProxyStrategyResponseBuilderInterface
     */
    public function create();

    /**
     * @return ServiceProxyStrategyResponseBuilderInterface
     */
    public function withPreSource($preSource);

    /**
     * @return ServiceProxyStrategyResponseBuilderInterface
     */
    public function withPostSource($postSource);

    /**
     * @return ServiceProxyStrategyResponseBuilderInterface
     */
    public function withExceptionSource($exceptionSource);

    /**
     * @return ServiceProxyStrategyResponseBuilderInterface
     */
    public function withMethods(array $methods);

    /**
     * @return ServiceProxyStrategyResponseInterface
     */
    public function build();
}
