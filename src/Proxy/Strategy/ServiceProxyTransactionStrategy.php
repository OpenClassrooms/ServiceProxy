<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Proxy\Strategy;

use Laminas\Code\Generator\AbstractMemberGenerator;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use OpenClassrooms\ServiceProxy\Annotations\Transaction;
use OpenClassrooms\ServiceProxy\Proxy\Strategy\Request\ServiceProxyStrategyRequestInterface;
use OpenClassrooms\ServiceProxy\Proxy\Strategy\Response\ServiceProxyStrategyResponseBuilderInterface;
use OpenClassrooms\ServiceProxy\Proxy\Strategy\Response\ServiceProxyStrategyResponseInterface;
use OpenClassrooms\ServiceProxy\Transaction\TransactionAdapterInterface;

class ServiceProxyTransactionStrategy implements ServiceProxyStrategyInterface
{
    private ServiceProxyStrategyResponseBuilderInterface $serviceProxyStrategyResponseBuilder;

    public function execute(ServiceProxyStrategyRequestInterface $request): ServiceProxyStrategyResponseInterface
    {
        $annotation = $request->getAnnotation();

        return $this->serviceProxyStrategyResponseBuilder
            ->create()
            ->withPreSource($this->generatePreSource())
            ->withPostSource($this->generatePostSource())
            ->withExceptionSource($this->generateExceptionSource($annotation))
            ->withProperties($this->generateProperties())
            ->withMethods($this->generateMethods())
            ->build();
    }

    private function generatePreSource(): string
    {
        $template = <<<PHP
if (!%s) {
    %s
}
PHP;

        return sprintf(
            $template,
            '$this->' . self::PROPERTY_PREFIX . 'transactionAdapter->isTransactionActive()',
            '$this->' . self::PROPERTY_PREFIX . 'transactionAdapter->beginTransaction();'
        );
    }

    private function generatePostSource(): string
    {
        return '$this->' . self::PROPERTY_PREFIX . 'transactionAdapter->commit();';
    }

    private function generateExceptionSource(Transaction $annotation): string
    {
        if (null === $annotation->getOnConflictThrow()) {
            return '$this->' . self::PROPERTY_PREFIX . 'transactionAdapter->rollback();';
        }

        $template = '
$this->%stransactionAdapter->rollback();
if ($e instanceof \OpenClassrooms\ServiceProxy\Exceptions\TransactionConflictException) {
    throw new %s(\'\', 0, $e);
}';

        return sprintf($template, self::PROPERTY_PREFIX, $annotation->getOnConflictThrow());
    }

    /**
     * @return MethodGenerator[]
     */
    public function generateMethods(): array
    {
        return [
            new MethodGenerator(
                self::METHOD_PREFIX . 'setTransactionAdapter',
                [
                    [
                        'name' => 'transactionAdapter',
                        'type' => TransactionAdapterInterface::class,
                    ],
                ],
                AbstractMemberGenerator::FLAG_PUBLIC,
                '$this->' . self::PROPERTY_PREFIX . 'transactionAdapter = $transactionAdapter;'
            ),
        ];
    }

    /**
     * @return PropertyGenerator[]
     */
    public function generateProperties(): array
    {
        return [new PropertyGenerator(self::PROPERTY_PREFIX.'transactionAdapter', null, AbstractMemberGenerator::FLAG_PRIVATE)];
    }

    public function setServiceProxyStrategyResponseBuilder(
        ServiceProxyStrategyResponseBuilderInterface $serviceProxyStrategyResponseBuilder
    ): void {
        $this->serviceProxyStrategyResponseBuilder = $serviceProxyStrategyResponseBuilder;
    }
}
