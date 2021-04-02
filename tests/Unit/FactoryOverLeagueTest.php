<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Unit;

use League\Container\Container;
use Psr\Container\ContainerInterface;

/**
 * Test the Factory over League Container.
 */
class FactoryOverLeagueTest extends AbstractFactoryTest
{
    public function createContainer(iterable $definitions = []): ContainerInterface
    {
        $container = new Container();
        foreach ($definitions as $id => $definition) {
            $container->add($id, $definition);
        }

        return $container;
    }
}
