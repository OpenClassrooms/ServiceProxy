<?php

namespace OpenClassrooms\ServiceProxy\Proxy\Strategy\Response;

/**
 * @author Romain Kuzniak <romain.kuzniak@openclassrooms.com>
 */
interface ServiceProxyStrategyResponseInterface
{
    /**
     * @return string
     */
    public function getPreSource();

    /**
     * @return string
     */
    public function getPostSource();

    /**
     * @return string
     */
    public function getExceptionSource();

    /**
     * @return array
     */
    public function getMethods();
}
