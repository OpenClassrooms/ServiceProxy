<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Double\Stub;

class WithoutAnnotationClass
{
    /**
     * @var mixed
     */
    public $field;

    public function aMethodWithoutAnnotation(): bool
    {
        return $this->field;
    }

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
