<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Proxy;

use ProxyManager\Proxy\ProxyInterface;

/**
 * @template T of object
 *
 * @phpstan-extends ProxyInterface<T>
 */
interface AccessInterceptorsInterface extends ProxyInterface
{
    /**
     * @param array<string, list<\Closure(AccessInterceptorsInterface<T>, object, string, array<string, mixed>, bool): mixed>> $prefixInterceptors
     */
    public function setPrefixInterceptors(array $prefixInterceptors): void;

    /**
     * @param array<string, list<\Closure(AccessInterceptorsInterface<T>, object, string, array<string, mixed>, bool): mixed>> $suffixInterceptors
     */
    public function setSuffixInterceptors(array $suffixInterceptors): void;
}
