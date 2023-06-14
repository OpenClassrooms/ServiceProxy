<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Interceptor\Interceptor;

use OpenClassrooms\ServiceProxy\Attribute\InvalidateCache;
use OpenClassrooms\ServiceProxy\Handler\Contract\CacheHandler;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\AbstractInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\SuffixInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Request\Instance;
use OpenClassrooms\ServiceProxy\Interceptor\Response\Response;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class InvalidateCacheInterceptor extends AbstractInterceptor implements SuffixInterceptor
{
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
     * @param mixed[] $parameters
     */
    private function resolveExpression(string $expression, array $parameters): string
    {
        $expressionLanguage = new ExpressionLanguage();
        $resolvedExpression = $expressionLanguage->evaluate(
            $expression,
            $parameters
        );

        if (!\is_string($resolvedExpression)) {
            throw new \InvalidArgumentException(
                "Provided expression `{$expression}` did not resolve to a string."
            );
        }

        return $resolvedExpression;
    }

    /**
     * @return array<int, string>
     */
    private function getTags(Instance $instance, InvalidateCache $attribute): array
    {
        $parameters = $instance->getMethod()
            ->getParameters();
        $tags = [];
        foreach ($attribute->getTags() as $tag) {
            $tags[] = $this->resolveExpression(
                $tag,
                $parameters
            );
        }

        return array_filter($tags);
    }
}
