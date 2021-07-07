<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use stdClass;
use Yiisoft\Factory\Exception\NotInstantiableException;

final class NotInstantiableExceptionTest extends TestCase
{
    public function testWithoutMessage(): void
    {
        $exception = new NotInstantiableException(stdClass::class);

        $this->assertSame('Can not instantiate stdClass.', $exception->getMessage());
    }

    public function testWithMessage(): void
    {
        $exception = new NotInstantiableException(stdClass::class, 'Test message.');

        $this->assertSame('Test message.', $exception->getMessage());
    }
}
