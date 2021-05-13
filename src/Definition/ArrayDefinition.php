<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Definition;

use Psr\Container\ContainerInterface;
use Yiisoft\Factory\Exception\InvalidConfigException;
use Yiisoft\Factory\Exception\NotInstantiableException;

/**
 * Builds object by array config
 *
 * @psalm-type MethodOrPropertyItem = array{0:string,1:string,2:mixed}
 */
class ArrayDefinition implements DefinitionInterface
{
    public const CLASS_NAME = 'class';
    public const CONSTRUCTOR = '__construct()';

    public const FLAG_PROPERTY = 'property';
    public const FLAG_METHOD = 'method';

    /**
     * @psalm-var class-string
     */
    private string $class;
    private array $constructorArguments;

    /**
     * @psalm-var array<string, MethodOrPropertyItem>
     */
    private array $methodsAndProperties;

    /**
     * @psalm-param class-string $class
     * @psalm-param array<string, MethodOrPropertyItem> $methodsAndProperties
     */
    private function __construct(string $class, array $constructorArguments, array $methodsAndProperties)
    {
        $this->class = $class;
        $this->constructorArguments = $constructorArguments;
        $this->methodsAndProperties = $methodsAndProperties;
    }

    /**
     * @throws InvalidConfigException
     */
    public static function fromConfig(array $config): self
    {
        return new self(
            $config[self::CLASS_NAME],
            $config[self::CONSTRUCTOR] ?? [],
            self::getMethodsAndPropertiesFromConfig($config)
        );
    }

    /**
     * @psalm-param class-string $class
     * @psalm-param array<string, MethodOrPropertyItem> $methodsAndProperties
     */
    public static function fromPreparedData(string $class, array $constructorArguments = [], array $methodsAndProperties = []): self
    {
        return new self($class, $constructorArguments, $methodsAndProperties);
    }

    /**
     * @psalm-return array<string, MethodOrPropertyItem>
     *
     * @throws InvalidConfigException
     */
    private static function getMethodsAndPropertiesFromConfig(array $config): array
    {
        $methodsAndProperties = [];

        foreach ($config as $key => $value) {
            if ($key === self::CONSTRUCTOR) {
                continue;
            }
            if (substr($key, -2) === '()') {
                $methodsAndProperties[$key] = [self::FLAG_METHOD, $key, $value];
            } elseif (strncmp($key, '$', 1) === 0) {
                $methodsAndProperties[$key] = [self::FLAG_PROPERTY, $key, $value];
            }
        }

        return $methodsAndProperties;
    }

    /**
     * @psalm-return class-string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    public function getConstructorArguments(): array
    {
        return $this->constructorArguments;
    }

    public function setConstructorArguments(array $arguments): void
    {
        $this->constructorArguments = $arguments;
    }

    /**
     * @psalm-return array<string, MethodOrPropertyItem>
     */
    public function getMethodsAndProperties(): array
    {
        return $this->methodsAndProperties;
    }

    /**
     * @throws NotInstantiableException
     * @throws InvalidConfigException
     */
    public function resolve(ContainerInterface $container): object
    {
        return ArrayDefinitionBuilder::getInstance()->build($container, $this);
    }

    public function merge(self $other): self
    {
        $new = clone $this;
        $new->class = $other->class;
        $new->constructorArguments = $this->mergeArguments($this->constructorArguments, $other->constructorArguments);

        $methodsAndProperties = $this->methodsAndProperties;
        foreach ($other->methodsAndProperties as $key => $item) {
            if ($item[0] === self::FLAG_PROPERTY) {
                $methodsAndProperties[$key] = $item;
            } elseif ($item[0] === self::FLAG_METHOD) {
                /** @psalm-suppress MixedArgument */
                $methodsAndProperties[$key] = [
                    $item[0],
                    $item[1],
                    isset($methodsAndProperties[$key])
                        ? $this->mergeArguments($methodsAndProperties[$key][2], $item[2])
                        : $item[2],
                ];
            }
        }
        $new->methodsAndProperties = $methodsAndProperties;

        return $new;
    }

    private function mergeArguments(array $selfArguments, array $otherArguments): array
    {
        /** @var mixed $argument */
        foreach ($otherArguments as $name => $argument) {
            /** @var mixed */
            $selfArguments[$name] = $argument;
        }

        return $selfArguments;
    }
}
