<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Unit\Exception;

use LogicException;
use PHPUnit\Framework\TestCase;
use stdClass;
use Yiisoft\Factory\Exception\NotInstantiableClassException;

final class NotInstantiableClassExceptionTest extends TestCase
{
    public function testDefaultArguments(): void
    {
        $exception = new NotInstantiableClassException(stdClass::class);

        $this->assertSame(0, $exception->getCode());
        $this->assertSame('Can not instantiate stdClass.', $exception->getMessage());
        $this->assertNull($exception->getPrevious());
    }

    public function testWithMessage(): void
    {
        $exception = new NotInstantiableClassException(stdClass::class, 'Test message.');

        $this->assertSame('Test message.', $exception->getMessage());
    }

    public function testWithCode(): void
    {
        $exception = new NotInstantiableClassException(stdClass::class, null, 99);

        $this->assertSame(99, $exception->getCode());
    }

    public function testWithPreviousException(): void
    {
        $previousException = new LogicException();
        $exception = new NotInstantiableClassException(stdClass::class, null, 0, $previousException);

        $this->assertSame($previousException, $exception->getPrevious());
    }
}
