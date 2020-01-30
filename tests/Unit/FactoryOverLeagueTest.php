<?php

namespace Yiisoft\Factory\Tests\Unit;

use Psr\Container\ContainerInterface;
use League\Container\Container;

/**
 * Test the Factory over League Container.
 */
class FactoryOverLeagueTest extends AbstractFactoryTest
{
    public function createContainer(iterable $definitions = []): ContainerInterface
    {
        return $this->setupContainer(new Container(), $definitions);
    }

    public function setupContainer(ContainerInterface $container, iterable $definitions = []): ContainerInterface
    {
        foreach ($definitions as $id => $definition) {
            $container->add($id, $definition);
        }

        return $container;
    }
}
