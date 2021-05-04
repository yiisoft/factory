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
    public static function normalize($definition, string $id = null, array $constructorArguments = []): DefinitionInterface
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
            return new ArrayDefinition($config);
        }

        if (is_object($definition)) {
            return new ValueDefinition($definition);
        }

        throw new InvalidConfigException('Invalid definition:' . var_export($definition, true));
    }
}
