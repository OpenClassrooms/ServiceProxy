<?php

namespace OpenClassrooms\ServiceProxy\Proxy\Strategy\Response;

use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\PropertyGenerator;

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
     * @return PropertyGenerator[]
     */
    public function getProperties();

    /**
     * @return MethodGenerator[]
     */
    public function getMethods();
}
