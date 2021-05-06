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
 * Class Definition represents a definition in a container
 */
class Normalizer
{
    private const DEFINITION_META = 'definition';

    /**
     * Definition may be defined multiple ways.
     * Interface name as string:
     *
     * ```php
     * $container->set('interface_name', EngineInterface::class);
     * ```
     *
     * A closure:
     *
     * ```php
     * $container->set('closure', function($container) {
     *     return new MyClass($container->get('db'));
     * });
     * ```
     *
     * A callable array:
     *
     * ```php
     * $container->set('static_call', [MyClass::class, 'create']);
     * ```
     *
     * A definition array:
     *
     * ```php
     * $container->set('full_definition', [
     *     'class' => EngineMarkOne::class,
     *     '__construct()' => [42],
     *     '$argName' => 'value',
     *     'setX()' => [42],
     * ]);
     * ```
     *
     * @param mixed $definition
     *
     * @throws InvalidConfigException
     */
    public static function normalize($definition, string $id = null, array $constructorArguments = [], bool $checkDefinition = true): DefinitionInterface
    {
        if ($definition instanceof DefinitionInterface) {
            return $definition;
        }

        if (is_string($definition)) {
            if (empty($definition)) {
                throw new InvalidConfigException('Invalid definition: empty string.');
            }
            if ($id === $definition || (!empty($constructorArguments) && class_exists($definition))) {
                /** @psalm-var class-string $definition */
                return new ArrayDefinition([
                    ArrayDefinition::CLASS_NAME => $definition,
                    ArrayDefinition::CONSTRUCTOR => $constructorArguments,
                ]);
            }
            return Reference::to($definition);
        }

        if (is_callable($definition, true)) {
            return new CallableDefinition($definition);
        }

        if (is_array($definition)) {
            $config = $definition;
            if (!array_key_exists(ArrayDefinition::CLASS_NAME, $config)) {
                $config[ArrayDefinition::CLASS_NAME] = $id;
            }
            /** @psalm-suppress ArgumentTypeCoercion */
            return new ArrayDefinition($config, $checkDefinition);
        }

        if (is_object($definition)) {
            return new ValueDefinition($definition);
        }

        throw new InvalidConfigException('Invalid definition:' . var_export($definition, true));
    }

    /**
     * Validates definition for correctness.
     *
     * @param mixed $definition {@see normalize()}
     *
     * @throws InvalidConfigException
     */
    public static function validate($definition, bool $throw = true): bool
    {
        if ($definition instanceof ReferenceInterface) {
            return true;
        }

        if (is_string($definition) && !empty($definition)) {
            return true;
        }

        if (is_callable($definition)) {
            return true;
        }

        if (is_array($definition)) {
            return true;
        }

        if (is_object($definition)) {
            return true;
        }

        if ($throw) {
            throw new InvalidConfigException('Invalid definition:' . var_export($definition, true));
        }

        return false;
    }

    /**
     * Validates definition for correctness.
     *
     * @param mixed $definition
     * @param array $allowedMeta
     *
     * @throws InvalidConfigException
     */
    public static function parse($definition, array $allowedMeta): array
    {
        if (!is_array($definition)) {
            return [$definition, []];
        }

        $meta = [];
        if (isset($definition[self::DEFINITION_META])) {
            $newDefinition = $definition[self::DEFINITION_META];
            unset($definition[self::DEFINITION_META]);
            $meta = array_filter($definition, static function ($key) use ($allowedMeta) {
                return in_array($key, $allowedMeta, true);
            }, ARRAY_FILTER_USE_KEY);
            $definition = $newDefinition;
        }

        if (is_callable($definition, true)) {
            return [$definition, $meta];
        }

        foreach ($definition as $key => $value) {
            // Method.
            if ($key === ArrayDefinition::CLASS_NAME || $key === ArrayDefinition::CONSTRUCTOR) {
                continue;
            }
            if (substr($key, -2) === '()') {
                if (!is_array($value)) {
                    throw new InvalidConfigException(
                        sprintf('Invalid definition: incorrect method arguments. Expected array, got %s.', self::getType($value))
                    );
                }
                // Not property = meta.
            } elseif (strpos($key, '$') !== 0) {
                if ($allowedMeta === [] || !in_array($key, $allowedMeta, true)) {
                    throw new InvalidConfigException(sprintf('Invalid definition: metadata "%s" is not allowed. Did you mean "%s()" or "$%s"?', $key, $key, $key));
                }
                $meta[$key] = $value;
                unset($definition[$key]);
            }
        }

        return [$definition, $meta];
    }

    /**
     * @param mixed $value
     */
    private static function getType($value): string
    {
        return is_object($value) ? get_class($value) : gettype($value);
    }
}
