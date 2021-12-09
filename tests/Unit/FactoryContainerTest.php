<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Unit;

use LogicException;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;
use Yiisoft\Factory\FactoryContainer;
use Yiisoft\Test\Support\Container\SimpleContainer;

final class FactoryContainerTest extends TestCase
{
    public function dataHas(): array
    {
        return [
            'no "test" in the factory without container' => [
                false,
                null,
            ],
            'no "test" in the factory with empty container' => [
                false,
                new SimpleContainer(),
            ],
            '"test" is in the factory with container that has "test"' => [
                true,
                new SimpleContainer(['test' => new stdClass()]),
            ],
        ];
    }

    /**
     * @dataProvider dataHas
     */
    public function testHas(bool $expected, ?ContainerInterface $container): void
    {
        $factoryContainer = new FactoryContainer($container);
        $this->assertSame($expected, $factoryContainer->has('test'));
    }

    public function testGetNonExistsDefinition(): void
    {
        $factoryContainer = new FactoryContainer(null);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('No definition found for "non-exists".');
        $factoryContainer->getDefinition('non-exists');
    }
}
