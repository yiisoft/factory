<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Definition;

use Psr\Container\ContainerInterface;

/**
 * Interface DefinitionInterface
 *
 * @package yii\di\contracts
 */
interface DefinitionInterface
{
    /**
     * @param ContainerInterface $container
     *
     * @return mixed|object
     */
    public function resolve(ContainerInterface $container);
}
