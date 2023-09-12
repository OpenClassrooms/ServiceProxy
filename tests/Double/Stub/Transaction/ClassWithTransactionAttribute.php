<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Double\Stub\Transaction;

use OpenClassrooms\ServiceProxy\Attribute\Transaction;

class ClassWithTransactionAttribute
{
    #[Transaction]
    public function methodWithException(): void
    {
        throw new \RuntimeException();
    }

    #[Transaction]
    public function method(): void
    {
    }

    #[Transaction(exceptions: [
        "\RuntimeException" => "\InvalidArgumentException",
    ])]
    public function methodWithExceptionMappingThatThrowsException(): void
    {
        throw new \RuntimeException();
    }

    #[Transaction]
    public function nestedMethod(): void
    {
        $self = new self();
        $self->method();
    }

    #[Transaction(exceptions: [
        "\RuntimeException" => "\InvalidArgumentException",
    ])]
    public function nestedMethodThatThrowsException(): void
    {
        $self = new self();
        $self->methodWithException();
    }
}
