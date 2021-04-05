<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Definitions;

use Yiisoft\Factory\Exceptions\InvalidConfigException;

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
    public const TAGS = '__tags';
    public const DEFINITION = '__definition';

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
     *
     * @throws InvalidConfigException
     */
    public static function normalize($definition, string $id = null, array $params = []): DefinitionInterface
    {
        if ($definition instanceof DefinitionInterface) {
            return $definition;
        }

        if (is_string($definition)) {
            if (empty($definition)) {
                throw new InvalidConfigException('Invalid definition: empty string.');
            }
            if ($id === $definition || (!empty($params) && class_exists($definition))) {
                /** @psalm-var class-string $definition */
                return new ArrayDefinition([
                    'class' => $definition,
                    'constructor' => $params,
                ]);
            }
            return Reference::to($definition);
        }

        if (is_callable($definition, true)) {
            return new CallableDefinition($definition);
        }

        if (is_array($definition)) {
            $config = $definition;
            if (!array_key_exists('class', $config)) {
                $config['class'] = $id;
            }
            /** @psalm-suppress ArgumentTypeCoercion */
            return new ArrayDefinition($config);
        }

        if (is_object($definition)) {
            return new ValueDefinition($definition);
        }

        throw new InvalidConfigException('Invalid definition:' . var_export($definition, true));
    }

    /**
     * @param mixed $definition
     */
    public static function parse($definition): array
    {
        if (!is_array($definition)) {
            return [$definition, []];
        }
        $tags = (array)($definition[self::TAGS] ?? []);
        unset($definition[self::TAGS]);

        return [$definition[self::DEFINITION] ?? $definition, $tags];
    }

    /**
     * Validates defintion for corectness.
     *
     * @param mixed $definition {@see normalize()}
     *
     * @throws InvalidConfigException
     */
    public static function validate($definition, bool $throw = true): bool
    {
        if ($definition instanceof DefinitionInterface) {
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
}
