<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Interceptor\Impl;

use OpenClassrooms\ServiceProxy\Attribute\InvalidateCache;
use OpenClassrooms\ServiceProxy\ExpressionLanguage\ExpressionResolver;
use OpenClassrooms\ServiceProxy\Handler\Contract\CacheHandler;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\AbstractInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\SuffixInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Request\Instance;
use OpenClassrooms\ServiceProxy\Interceptor\Response\Response;

final class InvalidateCacheInterceptor extends AbstractInterceptor implements SuffixInterceptor
{
    private ExpressionResolver $expressionResolver;

    public function __construct(iterable $handlers = [])
    {
        parent::__construct($handlers);

        $this->expressionResolver = new ExpressionResolver();
    }

    public function suffix(Instance $instance): Response
    {
        if ($instance->getMethod()->threwException()) {
            return new Response();
        }

        $attribute = $instance->getMethod()
            ->getAttribute(InvalidateCache::class);

        $handler = $this->getHandler(CacheHandler::class, $attribute);

        $tags = $this->getTags($instance, $attribute);

        $handler->invalidateTags($tags);

        return new Response();
    }

    public function supportsSuffix(Instance $instance): bool
    {
        return $instance->getMethod()
            ->hasAttribute(InvalidateCache::class);
    }

    public function getSuffixPriority(): int
    {
        return 40;
    }

    /**
     * @return array<int, string>
     */
    private function getTags(Instance $instance, InvalidateCache $attribute): array
    {
        $parameters = $instance->getMethod()
            ->getParameters();

        $tags = array_map(
            fn (string $expression) => $this->expressionResolver->resolve($expression, $parameters),
            $attribute->getTags()
        );

        return array_filter($tags);
    }
}
