<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Handler\Contract;

interface SecurityHandler extends AnnotationHandler
{
    /**
     * @param string[] $attributes
     * @param mixed $param
     *
     * @throws \Exception
     */
    public function checkAccess(array $attributes, $param = null): void;
}
