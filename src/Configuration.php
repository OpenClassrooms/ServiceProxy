<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy;

final class Configuration
{
    private string $proxiesDir;

    public function __construct(?string $proxiesDir = null)
    {
        $this->proxiesDir = $proxiesDir ?? sys_get_temp_dir() . '/proxies';
    }

    public function getProxiesDir(): string
    {
        return $this->proxiesDir;
    }
}
