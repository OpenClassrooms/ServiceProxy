<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Interceptor\Impl;

use OpenClassrooms\ServiceProxy\Annotation\Transaction;
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
        $annotation = $instance->getMethod()
            ->getAnnotation(Transaction::class);
        $handler = $this->getHandler(TransactionHandler::class, $annotation);
        $handler->begin();

        return new Response();
    }

    /**
     * @throws \Exception
     */
    public function suffix(Instance $instance): Response
    {
        $annotation = $instance->getMethod()
            ->getAnnotation(Transaction::class);
        $handler = $this->getHandler(TransactionHandler::class, $annotation);
        if ($instance->getMethod()->threwException()) {
            $handler->rollback();

            if ($annotation->hasMappedExceptions()) {
                $this->handleMappedException($instance, $annotation);
            }
        } else {
            $handler->commit();
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
            ->hasAnnotation(Transaction::class);
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
    private function handleMappedException(Instance $instance, Transaction $annotation): void
    {
        $thrownException = $instance->getMethod()
            ->getException();

        if ($thrownException instanceof \Exception) {
            foreach ($annotation->getExceptions() as $fromException => $toException) {
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
