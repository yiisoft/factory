<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Definition;

use Psr\Container\ContainerInterface;
use Yiisoft\Factory\Exception\NotFoundException;

/**
 * An invalid dependency is created when a parameter has no type and no default value.
 * For example:
 * ```php
 * public function __construct($a, $b) {}
 * ```
 *
 * These dependency must be replaced, attempting to resolve them will throw an exception
 */
class InvalidDefinition implements DefinitionInterface
{
    public function resolve(ContainerInterface $container)
    {
        throw new NotFoundException('Invalid reference');
    }
}
