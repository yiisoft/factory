<?php

declare(strict_types=1);

namespace Definition;

use PHPUnit\Framework\TestCase;
use Yiisoft\Factory\Definition\ArrayDefinition;
use Yiisoft\Factory\Definition\DefinitionValidator;
use Yiisoft\Factory\Exception\InvalidConfigException;
use Yiisoft\Factory\Tests\Support\Phone;

final class DefinitionValidatorTest extends TestCase
{
    public function testIntegerKeyOfArray(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('Invalid definition: invalid key in array definition. Allow only string keys, got 0.');
        DefinitionValidator::validate([
            ArrayDefinition::CLASS_NAME => Phone::class,
            'RX',
        ]);
    }

    public function dataInvalidClass(): array
    {
        return [
            [42, 'Invalid definition: invalid class name. Expected string, got integer.'],
            ['', 'Invalid definition: empty class name.'],
        ];
    }

    /**
     * @dataProvider dataInvalidClass
     */
    public function testInvalidClass($class, string $message): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage($message);
        DefinitionValidator::validate([
            ArrayDefinition::CLASS_NAME => $class,
        ]);
    }

    public function testWithoutClass(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('Invalid definition: no class name specified.');
        DefinitionValidator::validate([]);
    }

    public function testInvalidConstructor(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('Invalid definition: incorrect constructor arguments. Expected array, got string.');
        DefinitionValidator::validate([
            ArrayDefinition::CLASS_NAME => Phone::class,
            ArrayDefinition::CONSTRUCTOR => 'Kiradzu',
        ]);
    }

    public function dataInvalidMethodCalls(): array
    {
        return [
            [['addApp()' => 'Browser'], 'Invalid definition: incorrect method arguments. Expected array, got string.'],
        ];
    }

    /**
     * @dataProvider dataInvalidMethodCalls
     */
    public function testInvalidMethodCalls($methodCalls, string $message): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage($message);
        DefinitionValidator::validate(array_merge(
            [
                ArrayDefinition::CLASS_NAME => Phone::class,
            ],
            $methodCalls
        ));
    }

    public function testErrorOnPropertyTypo(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('Invalid definition: key "dev" is not allowed. Did you mean "dev()" or "$dev"?');
        DefinitionValidator::validate([
            'class' => Phone::class,
            'dev' => true,
        ]);
    }

    public function testErrorOnMethodTypo(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('Invalid definition: key "setId" is not allowed. Did you mean "setId()" or "$setId"?');
        DefinitionValidator::validate([
            'class' => Phone::class,
            'setId' => [42],
        ]);
    }
}
