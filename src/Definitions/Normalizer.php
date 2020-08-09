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
     * @param mixed $config
     * @param string $id
     * @param array $params
     * @throws InvalidConfigException
     */
    public static function normalize($config, string $id = null, array $params = []): DefinitionInterface
    {
        if ($config instanceof ReferenceInterface) {
            return $config;
        }

        if (\is_string($config)) {
            if ($id === $config || (!empty($params) && class_exists($config))) {
                return ArrayDefinition::fromArray($config, $params);
            }
            return Reference::to($config);
        }

        if (\is_callable($config)) {
            return new CallableDefinition($config);
        }

        if (\is_array($config)) {
            return ArrayDefinition::fromArray($id, [], $config);
        }

        if (\is_object($config)) {
            return new ValueDefinition($config);
        }

        throw new InvalidConfigException('Invalid definition:' . var_export($config, true));
    }
}
