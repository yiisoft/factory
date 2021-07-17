<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Definition;

use Yiisoft\Factory\Exception\InvalidConfigException;

use function array_key_exists;
use function is_array;
use function is_callable;
use function is_object;
use function is_string;

/**
 * Normalizer definition from configuration to an instance of `DefinitionInterface`.
 *
 * @psalm-import-type ArrayDefinitionConfig from ArrayDefinition
 */
final class Normalizer
{
    /**
     * Normalize definition to an instance of `DefinitionInterface`.
     * Definition may be defined multiple ways:
     *  - class name,
     *  - string as reference to another class or alias,
     *  - instance of `ReferenceInterface`,
     *  - callable,
     *  - array,
     *  - ready object.
     *
     * @param mixed $definition The definition for normalization.
     * @param string $class The class name of the object to be defined (optional). It is used in two cases.
     *  - The definition is a string, and class name equals to definition. Returned `ArrayDefinition` with defined
     *    class.
     *  - The definition is an array without class name. Class name will be added to array and `ArrayDefinition`
     *    will be returned.
     *
     * @throws InvalidConfigException If configuration is not valid.
     *
     * @return DefinitionInterface Normalized definition as an object.
     */
    public static function normalize($definition, string $class = null): DefinitionInterface
    {
        // Reference
        if ($definition instanceof ReferenceInterface) {
            return $definition;
        }

        if (is_string($definition)) {
            // Current class
            if (
                $class === $definition ||
                ($class === null && class_exists($definition))
            ) {
                /** @psalm-var class-string $definition */
                return ArrayDefinition::fromPreparedData($definition);
            }

            // Reference to another class or alias
            return Reference::to($definition);
        }

        // Callable definition
        if (is_callable($definition, true)) {
            return new CallableDefinition($definition);
        }

        // Array definition
        if (is_array($definition)) {
            $config = $definition;
            if (!array_key_exists(ArrayDefinition::CLASS_NAME, $config)) {
                $config[ArrayDefinition::CLASS_NAME] = $class;
            }
            /** @psalm-var ArrayDefinitionConfig $config */
            return ArrayDefinition::fromConfig($config);
        }

        // Ready object
        if (is_object($definition)) {
            return new ValueDefinition($definition);
        }

        throw new InvalidConfigException('Invalid definition:' . var_export($definition, true));
    }
}
