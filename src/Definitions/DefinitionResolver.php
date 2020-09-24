<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Definitions;

use Psr\Container\ContainerInterface;

class DefinitionResolver
{
    /**
     * Resolves dependencies by replacing them with the actual object instances.
     * @param ContainerInterface $container
     * @param DefinitionInterface[] $dependencies the dependencies
     * @return array the resolved dependencies
     */
    public static function resolveArray(ContainerInterface $container, array $dependencies): array
    {
        $result = [];
        foreach ($dependencies as $key => $definition) {
            $result[$key] = self::resolve($container, $definition);
        }

        return $result;
    }

    /**
     * This function resolves a definition recursively, checking for loops.
     * @param ContainerInterface $container
     * @param mixed $definition
     * @return mixed
     */
    public static function resolve(ContainerInterface $container, $definition)
    {
        if ($definition instanceof DefinitionInterface) {
            $definition = $definition->resolve($container);
        } elseif (is_array($definition)) {
            return self::resolveArray($container, $definition);
        }

        return $definition;
    }

    public static function ensureResolvable($value)
    {
        if ($value instanceof DefinitionInterface || is_array($value)) {
            return $value;
        }
        if (is_callable($value)) {
            return new CallableDefinition($value);
        }

        return new ValueDefinition($value);
    }
}
