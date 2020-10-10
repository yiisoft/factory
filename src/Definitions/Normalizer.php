<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Definitions;

use Yiisoft\Factory\Exceptions\InvalidConfigException;

/**
 * Class Definition represents a definition in a container
 */
class Normalizer
{
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
     *     '__class' => EngineMarkOne::class,
     *     '__construct()' => [42],
     *     'argName' => 'value',
     *     'setX()' => [42],
     * ]);
     * ```
     *
     * @param mixed $definition
     * @param string $id
     * @param array $params
     * @throws InvalidConfigException
     */
    public static function normalize($definition, string $id = null, array $params = []): DefinitionInterface
    {
        if ($definition instanceof DefinitionInterface) {
            return $definition;
        }

        if (\is_string($definition)) {
            if (empty($definition)) {
                throw new InvalidConfigException('Invalid definition: empty string');
            }
            if ($id === $definition || (!empty($params) && class_exists($definition))) {
                return ArrayDefinition::fromArray($definition, $params);
            }
            return Reference::to($definition);
        }

        if (\is_callable($definition, true)) {
            return new CallableDefinition($definition);
        }

        if (\is_array($definition)) {
            return ArrayDefinition::fromArray($id, [], $definition);
        }

        if (\is_object($definition)) {
            return new ValueDefinition($definition);
        }

        throw new InvalidConfigException('Invalid definition:' . var_export($definition, true));
    }

    /**
     * Validates defintion for corectness.
     * @param mixed $definition @see normalize()
     * @param bool $id
     * @return bool
     * @throws InvalidConfigException
     */
    public static function validate($definition, bool $throw = true): bool
    {
        if ($definition instanceof DefinitionInterface) {
            return true;
        }

        if (\is_string($definition) && !empty($definition)) {
            return true;
        }

        if (\is_callable($definition)) {
            return true;
        }

        if (\is_array($definition)) {
            return true;
        }

        if (\is_object($definition)) {
            return true;
        }

        if ($throw) {
            throw new InvalidConfigException('Invalid definition:' . var_export($definition, true));
        }

        return false;
    }
}
