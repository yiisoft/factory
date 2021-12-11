<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Yiisoft\Factory\NotFoundException;

final class NotFoundExceptionTest extends TestCase
{
    public function testGetId(): void
    {
        $exception = new NotFoundException('test');

        $this->assertSame('test', $exception->getId());
    }

    public function testMessage(): void
    {
        $exception = new NotFoundException('test');

        $this->assertSame('No definition or class found or resolvable for test.', $exception->getMessage());
    }
}
