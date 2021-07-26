<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Definition;

use Yiisoft\Factory\Exception\InvalidConfigException;
use Yiisoft\Factory\Exception\NotFoundException;
use Yiisoft\Factory\Exception\NotInstantiableException;
use Yiisoft\Factory\DependencyResolverInterface;

/**
 * Interface DefinitionInterface
 */
interface DefinitionInterface
{
    /**
     * @param DependencyResolverInterface $container
     *
     * @return mixed|object
     *@throws NotFoundException
     * @throws NotInstantiableException
     *
     * @throws InvalidConfigException
     */
    public function resolve(DependencyResolverInterface $container);
}
