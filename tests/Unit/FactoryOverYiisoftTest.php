<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Unit;

use Psr\Container\ContainerInterface;
use Yiisoft\Di\Container;

/**
 * Test the Factory over Yiisoft Container.
 */
class FactoryOverYiisoftTest extends AbstractFactoryTest
{
    public function createContainer(iterable $definitions = []): ContainerInterface
    {
        return new Container($definitions);
    }
}
