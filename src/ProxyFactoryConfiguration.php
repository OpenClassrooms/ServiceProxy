<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy;

final class ProxyFactoryConfiguration
{
    private string $proxiesDir;

    private bool $eval;

    public function __construct(?string $proxiesDir = null, bool $eval = false)
    {
        $this->proxiesDir = $proxiesDir ?? sys_get_temp_dir() . '/proxies';
        $this->eval = $eval;
    }

    public function getProxiesDir(): string
    {
        return $this->proxiesDir;
    }

    public function isEval(): bool
    {
        return $this->eval;
    }

    public function setEval(bool $eval): void
    {
        $this->eval = $eval;
    }
}
