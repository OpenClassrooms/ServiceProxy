<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Doubles;

class WithoutAnnotationClass
{
    /**
     * @var mixed
     */
    public $field;

    /**
     * @return mixed
     */
    public function aMethodWithoutAnnotation()
    {
        return $this->field;
    }

    /**
     * @param mixed $value
     */
    public function aSetterMethod($value): void
    {
        $this->field = $value;
    }

    public function aSetterMethodWithType(string $value): string
    {
        return $value;
    }

    public function aMethodWithoutServiceProxyAnnotation(): bool
    {
        return true;
    }
}
