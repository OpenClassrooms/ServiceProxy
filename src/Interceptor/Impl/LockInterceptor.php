<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Interceptor\Impl;

use OpenClassrooms\ServiceProxy\Attribute\Lock;
use OpenClassrooms\ServiceProxy\Handler\Contract\LockHandler;
use OpenClassrooms\ServiceProxy\Handler\Exception\LockException;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\AbstractInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\PrefixInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\SuffixInterceptor;
use OpenClassrooms\ServiceProxy\Model\Request\Instance;
use OpenClassrooms\ServiceProxy\Model\Response\Response;
use OpenClassrooms\ServiceProxy\Util\Expression;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class LockInterceptor extends AbstractInterceptor implements PrefixInterceptor, SuffixInterceptor
{
    private LoggerInterface $logger;

    /**
     * @param iterable<LockHandler> $handlers
     */
    public function __construct(
        iterable         $handlers = [],
        ?LoggerInterface $logger = null,
    ) {
        $this->logger = $logger ?? new NullLogger();
        parent::__construct($handlers);
    }

    public function getPrefixPriority(): int
    {
        return 1;
    }

    public function getSuffixPriority(): int
    {
        return 39;
    }

    public function prefix(Instance $instance): Response
    {
        $attribute = $instance->getMethod()->getAttribute(Lock::class);
        $handlers = $this->getHandlers(LockHandler::class, $attribute);
        $key = $this->computeLockingKey($instance);
        try {
            foreach ($handlers as $handler) {
                $handler->acquire($key);
                if (!$handler->isAcquired($key)) {
                    $this->logger->error('Failed to acquire lock, for key: ' . $key, [
                        'handler' => $handler->getName(),
                    ]);
                }
            }
        } catch (LockException $e) {
            $this->logger->error('Failed to acquire lock, for key: ' . $key, [
                'exception' => $e,
            ]);
        }

        return new Response();
    }

    public function suffix(Instance $instance): Response
    {
        $key = $this->computeLockingKey($instance);
        $attribute = $instance->getMethod()->getAttribute(Lock::class);
        $handlers = $this->getHandlers(LockHandler::class, $attribute);
        try {
            foreach ($handlers as $handler) {
                $handler->release($key);
                if ($handler->isAcquired($key)) {
                    $this->logger->error('Failed to release lock, for key: ' . $key, [
                        'handler' => $handler->getName(),
                    ]);
                }
            }
        } catch (LockException $e) {
            $this->logger->error('Failed to release lock, for key: ' . $key, [
                'exception' => $e,
            ]);
        }

        return new Response();
    }

    public function supportsSuffix(Instance $instance): bool
    {
        return $this->supportsPrefix($instance);
    }

    public function supportsPrefix(Instance $instance): bool
    {
        return $instance->getMethod()
            ->hasAttribute(Lock::class)
        ;
    }

    private function computeLockingKey(Instance $instance): string
    {
        $key = $instance->getMethod()->getAttribute(Lock::class)->key;

        return Expression::evaluateToString($key, $instance->getMethod()->getParameters());
    }
}
