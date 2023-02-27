<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Interceptor\Interceptor;

use OpenClassrooms\ServiceProxy\Annotation\Transaction;
use OpenClassrooms\ServiceProxy\Handler\Contract\TransactionHandler;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\AbstractInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\PrefixInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\SuffixInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Request\Instance;
use OpenClassrooms\ServiceProxy\Interceptor\Response\Response;

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

    public function suffix(Instance $instance): Response
    {
        $annotation = $instance->getMethod()
            ->getAnnotation(Transaction::class);
        $handler = $this->getHandler(TransactionHandler::class, $annotation);
        if ($instance->getMethod()->threwException()) {
            $handler->rollback();
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
}
