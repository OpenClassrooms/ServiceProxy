<?php

namespace OpenClassrooms\ServiceProxy\Tests;

/**
 * @author Romain Kuzniak <romain.kuzniak@openclassrooms.com>
 */
class ParameterClassStub
{
    /**
     * @var int
     */
    public $publicField = 1;

    /**
     * @var int
     */
    private $privateField = 2;

    /**
     * @return int
     */
    public function getPrivateField()
    {
        return $this->privateField;
    }
}
