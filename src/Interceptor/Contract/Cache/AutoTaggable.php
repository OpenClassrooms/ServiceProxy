<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Interceptor\Contract\Cache;

interface AutoTaggable
{
    public function getId(): string|int;
}
