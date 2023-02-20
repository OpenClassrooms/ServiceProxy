<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Contract;

interface SecurityHandler extends AnnotationHandler
{
    /**
     * @throws \Exception
     */
    public function checkAccess(array $attributes, $param = null);
}
