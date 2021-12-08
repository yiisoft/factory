<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Unit;

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
            [
                false,
                null,
            ],
            [
                false,
                new SimpleContainer(),
            ],
            [
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
}
