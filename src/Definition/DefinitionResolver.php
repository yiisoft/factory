<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Definition;

use Psr\Container\ContainerInterface;
use Yiisoft\Factory\Exception\InvalidConfigException;

use function is_array;
use function is_callable;
use function is_string;

class DefinitionResolver
{
    /**
     * Resolves dependencies by replacing them with the actual object instances.
     *
     * @param array<string,mixed> $dependencies The dependencies.
     *
     * @return array The resolved dependencies.
     * @psalm-return array<string,mixed>
     */
    public static function resolveArray(ContainerInterface $container, array $dependencies): array
    {
        $result = [];
        /** @var mixed $definition */
        foreach ($dependencies as $key => $definition) {
            if ($definition instanceof ParameterDefinition && !$definition->hasValue()) {
                continue;
            }

            /** @var mixed */
            $result[$key] = self::resolve($container, $definition);
        }

        return $result;
    }

    /**
     * This function resolves a definition recursively, checking for loops.
     *
     * @param mixed $definition
     *
     * @return mixed
     */
    public static function resolve(ContainerInterface $container, $definition)
    {
        if ($definition instanceof DefinitionInterface) {
            /** @var mixed $definition */
            $definition = $definition->resolve($container);
        } elseif (!is_string($definition) && !is_array($definition) && is_callable($definition, true)) {
            return (new CallableDefinition($definition))->resolve($container);
        } elseif (is_array($definition)) {
            /** @psalm-var array<string,mixed> $definition */
            return self::resolveArray($container, $definition);
        }

        return $definition;
    }

    /**
     * @param mixed $value
     *
     * @return array|CallableDefinition|DefinitionInterface|ValueDefinition
     */
    public static function ensureResolvable($value)
    {
        if ($value instanceof DefinitionInterface && !$value instanceof ReferenceInterface) {
            throw new InvalidConfigException('Only reference allowed in parameters, the definition object received:' . var_export($value, true));
        }

        if ($value instanceof ReferenceInterface || is_array($value)) {
            return $value;
        }

        return new ValueDefinition($value);
    }
}
