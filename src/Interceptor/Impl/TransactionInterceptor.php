<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Interceptor\Impl;

use OpenClassrooms\ServiceProxy\Attribute\Transaction;
use OpenClassrooms\ServiceProxy\Handler\Contract\TransactionHandler;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\AbstractInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\PrefixInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\SuffixInterceptor;
use OpenClassrooms\ServiceProxy\Model\Request\Instance;
use OpenClassrooms\ServiceProxy\Model\Response\Response;

final class TransactionInterceptor extends AbstractInterceptor implements PrefixInterceptor, SuffixInterceptor
{
    public function prefix(Instance $instance): Response
    {
        $attribute = $instance->getMethod()
                              ->getAttribute(Transaction::class);

        $handlers = $this->getHandlers(TransactionHandler::class, $attribute);
        foreach ($handlers as $handler) {
            $handler->begin($attribute->entityManagers);
        }

        return new Response();
    }

    /**
     * @throws \Exception
     */
    public function suffix(Instance $instance): Response
    {
        $attribute = $instance->getMethod()
                              ->getAttribute(Transaction::class);

        $handlers = $this->getHandlers(TransactionHandler::class, $attribute);
        foreach ($handlers as $handler) {
            if ($instance->getMethod()->threwException()) {
                $handler->rollback($attribute->entityManagers);

                if ($attribute->hasMappedExceptions()) {
                    $this->handleMappedException($instance, $attribute);
                }
            } else {
                $handler->commit($attribute->entityManagers);
            }
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
                        ->hasAttribute(Transaction::class);
    }

    public function getPrefixPriority(): int
    {
        return 0;
    }

    public function getSuffixPriority(): int
    {
        return 30;
    }

    /**
     * @throws \Exception
     */
    private function handleMappedException(Instance $instance, Transaction $attribute): void
    {
        $thrownException = $instance->getMethod()
                                    ->getException();

        if ($thrownException instanceof \Exception) {
            foreach ($attribute->exceptions as $fromException => $toException) {
                if (is_a($thrownException, $fromException)) {
                    $toThrow = new $toException();

                    if ($toThrow instanceof \Exception) {
                        throw $toThrow;
                    }
                }
            }
        }
    }
}
