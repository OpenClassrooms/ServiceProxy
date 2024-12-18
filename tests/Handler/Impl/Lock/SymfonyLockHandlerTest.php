<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Handler\Impl\Lock;

use OpenClassrooms\ServiceProxy\Handler\Impl\Lock\SymfonyLockHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\FlockStore;

class SymfonyLockHandlerTest extends TestCase
{
    protected function setUp(): void
    {
        $factory1 = new LockFactory(new FlockStore());
        $factory2 = new LockFactory(new FlockStore());
        $this->handler1 = new SymfonyLockHandler($factory1, false);
        $this->handler2 = new SymfonyLockHandler($factory2, false);
    }

    public function test1(): void
    {
        $this->handler1->acquire('key1');
        $this->assertTrue($this->handler1->isAcquired('key1'));
        $this->handler1->release('key1');
        $this->assertFalse($this->handler1->isAcquired('key1'));
    }

    public function test2(): void
    {
        $this->handler1->acquire('key1');
        $this->assertTrue($this->handler1->isAcquired('key1'));
        $this->handler1->acquire('key1');
        $this->assertTrue($this->handler1->isAcquired('key1'));
        $this->handler2->acquire('key2');
        $this->assertTrue($this->handler2->isAcquired('key2'));
        $this->handler1->release('key1');
        $this->assertFalse($this->handler1->isAcquired('key1'));
    }

    public function test3(): void
    {
        $this->handler1->acquire('key1');
        $this->assertTrue($this->handler1->isAcquired('key1'));
        $this->handler2->acquire('key1');
        $this->assertFalse($this->handler2->isAcquired('key1'));
        $this->handler1->release('key1');
        $this->assertFalse($this->handler1->isAcquired('key1'));
        $this->handler2->acquire('key1');
        $this->assertTrue($this->handler2->isAcquired('key1'));
        $this->handler2->release('key1');
        $this->assertFalse($this->handler2->isAcquired('key1'));
    }
}
