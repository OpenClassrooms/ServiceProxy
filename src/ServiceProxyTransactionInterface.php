<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy;

use OpenClassrooms\ServiceProxy\Transaction\TransactionAdapterInterface;

interface ServiceProxyTransactionInterface
{
    public function proxy_setTransactionAdapter(TransactionAdapterInterface $transactionAdapter);
}
