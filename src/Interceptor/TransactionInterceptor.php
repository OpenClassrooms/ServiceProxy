<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Interceptor;

use OpenClassrooms\ServiceProxy\Annotation\Transaction;
use OpenClassrooms\ServiceProxy\Contract\TransactionHandler;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\PrefixInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\SuffixInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Request\Instance;
use OpenClassrooms\ServiceProxy\Interceptor\Response\Response;

class TransactionInterceptor extends AbstractInterceptor implements PrefixInterceptor, SuffixInterceptor
{
    protected int $suffixPriority = 30;

    /**
     * @throws \Exception
     */
    public function prefix(Instance $instance): Response
    {
        $annotation = $instance->getMethod()->getAnnotation(Transaction::class);
        $handler = $this->getHandler(TransactionHandler::class, $annotation);
        $handler->beginTransaction();

        return new Response();
    }

    public function suffix(Instance $instance): Response
    {
        $annotation = $instance->getMethod()->getAnnotation(Transaction::class);
        $handler = $this->getHandler(TransactionHandler::class, $annotation);
        if ($instance->getMethod()->threwException()) {
            if ($handler->isTransactionActive()) {
                $handler->rollBack();
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
        return $instance->getMethod()->hasAnnotation(Transaction::class);
    }
}
