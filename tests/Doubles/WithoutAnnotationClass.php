<?php

namespace OpenClassrooms\ServiceProxy\Tests\Doubles;

/**
 * @author Romain Kuzniak <romain.kuzniak@openclassrooms.com>
 */
class WithoutAnnotationClass
{
    /**
     * @var mixed
     */
    public $field;

    /**
     * @return bool
     */
    public function aMethodWithoutAnnotation()
    {
        return $this->field;
    }

    public function aSetterMethod($value)
    {
        $this->field = $value;
    }

    /**
     * @return bool
     */
    public function aMethodWithoutServiceProxyAnnotation()
    {
        return true;
    }
}
