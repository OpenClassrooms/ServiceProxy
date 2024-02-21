<?php declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Proxy;

use Closure;
use ProxyManager\Proxy\ProxyInterface;


interface AccessInterceptorsInterface extends ProxyInterface
{

    public function setPrefixInterceptors(array $prefixInterceptors): void;

    public function setSuffixInterceptors(array $suffixInterceptors): void;
}
