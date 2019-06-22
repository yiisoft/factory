<?php

namespace Yiisoft\Factory\Tests\unit;

use Psr\Container\ContainerInterface;
use yii\di\Container;

/**
 * Test the Factory over Yiisoft Container.
 */
class FactoryOverYiisoftTest extends AbstractFactoryTest
{
    public function createContainer(iterable $definitions = []): ContainerInterface
    {
        return $this->setupContainer(new Container(), $definitions);
    }

    public function setupContainer(ContainerInterface $container, iterable $definitions = []): ContainerInterface
    {
        foreach ($definitions as $id => $definition) {
            $container->set($id, $definition);
        }

        return $container;
    }
}
