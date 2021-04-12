<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Definitions;

use Psr\Container\ContainerInterface;
use Yiisoft\Factory\Exceptions\InvalidConfigException;

use Yiisoft\Factory\Exceptions\NotInstantiableException;

use function array_key_exists;
use function get_class;
use function gettype;
use function is_array;
use function is_int;
use function is_object;
use function is_string;

/**
 * Builds object by array config
 */
class ArrayDefinition implements DefinitionInterface
{
    public const CLASS_NAME = 'class';
    public const CONSTRUCTOR = '__construct()';

    /**
     * @psalm-var class-string
     */
    private string $class;
    private array $constructorArguments;

    /**
     * @psalm-var array<string, array>
     */
    private array $callMethods;

    /**
     * @psalm-var array<string, mixed>
     */
    private array $setProperties;

    /**
     * @psalm-param array{
     *   class: class-string,
     *   constructor?: array,
     *   callMethods?: array,
     *   setProperties?: array,
     * } $config
     *
     * @throws InvalidConfigException
     */
    public function __construct(array $config)
    {
        $this->setClass($config);
        $this->setConstructorArguments($config);
        $this->setMethodCalls($config);
        $this->setSetProperties($config);
    }

    /**
     * @throws InvalidConfigException
     */
    private function setClass(array $config): void
    {
        if (!array_key_exists(self::CLASS_NAME, $config)) {
            throw new InvalidConfigException('Invalid definition: no class name specified.');
        }

        /** @var mixed */
        $class = $config[self::CLASS_NAME];

        if (!is_string($class)) {
            throw new InvalidConfigException(sprintf('Invalid definition: invalid class name "%s".', (string)$class));
        }

        if ($class === '') {
            throw new InvalidConfigException('Invalid definition: empty class name.');
        }

        if (!class_exists($class)) {
            throw new InvalidConfigException(sprintf('Invalid definition: class "%s" does not exist.', $class));
        }

        $this->class = $class;
    }

    /**
     * @throws InvalidConfigException
     */
    private function setConstructorArguments(array $config): void
    {
        $arguments = $config[self::CONSTRUCTOR] ?? [];

        if (!is_array($arguments)) {
            throw new InvalidConfigException(
                sprintf(
                    'Invalid definition: incorrect constructor arguments. Expected array, got %s.',
                    $this->getType($arguments)
                )
            );
        }

        $this->constructorArguments = $arguments;
    }

    /**
     * @throws InvalidConfigException
     */
    private function setMethodCalls(array $config): void
    {
        $items = $this->extractMethods($config);

        if (!is_array($items)) {
            throw new InvalidConfigException(
                sprintf('Invalid definition: incorrect method calls. Expected array, got %s.', $this->getType($items))
            );
        }

        $callMethods = [];
        foreach ($items as $key => $value) {
            if (is_int($key)) {
                if (!is_string($value)) {
                    throw new InvalidConfigException(
                        sprintf('Invalid definition: expected method name, got %s.', $this->getType($value))
                    );
                }
                if ($value === '') {
                    throw new InvalidConfigException('Invalid definition: expected method name, got empty string.');
                }
                $callMethods[$value] = [];
            } else {
                if (!is_array($value)) {
                    throw new InvalidConfigException(
                        sprintf('Invalid definition: incorrect method arguments. Expected array, got %s.', $this->getType($value))
                    );
                }
                $callMethods[$key] = $value;
            }
        }

        $this->callMethods = $callMethods;
    }

    private function extractMethods(array $config): array
    {
        $methods = [];

        foreach ($config as $key => $value) {
            if ($key !== self::CONSTRUCTOR && substr($key, -2) === '()') {
                $methods[substr($key, 0, -2)] = $value;
            }
        }

        return $methods;
    }

    /**
     * @throws InvalidConfigException
     */
    private function setSetProperties(array $config): void
    {
        $properties = $this->extractProperties($config);

        if (!is_array($properties)) {
            throw new InvalidConfigException(
                sprintf('Invalid definition: incorrect properties to set. Expected array, got %s.', $this->getType($properties))
            );
        }

        foreach ($properties as $key => $_value) {
            if (!is_string($key)) {
                throw new InvalidConfigException(
                    sprintf('Invalid definition: expected property name, got %s.', $this->getType($key))
                );
            }
        }

        /** @psalm-var array<string,mixed> $properties */

        $this->setProperties = $properties;
    }

    private function extractProperties(array $config): array
    {
        $properties = [];
        foreach ($config as $key => $value) {
            if (substr($key, 0, 1) === '@') {
                $properties[substr($key, 1)] = $value;
            }
        }

        return $properties;
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

    /**
     * @psalm-return array<string, array>
     */
    public function getMethodCalls(): array
    {
        return $this->callMethods;
    }

    /**
     * @psalm-return array<string, mixed>
     */
    public function getSetProperties(): array
    {
        return $this->setProperties;
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
        $callMethods = $this->getMethodCalls();
        foreach ($other->getMethodCalls() as $method => $arguments) {
            $callMethods[$method] = isset($callMethods[$method])
                ? $this->mergeArguments($callMethods[$method], $arguments)
                : $arguments;
        }
        $mergedMethods = [];
        foreach ($callMethods as $key => $value) {
            $mergedMethods[$key . '()'] = $value;
        }

        $properties = array_merge($this->getSetProperties(), $other->getSetProperties());
        $mergedProperties = [];
        foreach ($properties as $key => $value) {
            $mergedProperties['@' . $key] = $value;
        }

        return new self(array_merge([
            self::CLASS_NAME => $other->getClass(),
            self::CONSTRUCTOR => $this->mergeArguments(
                $this->getConstructorArguments(),
                $other->getConstructorArguments()
            ),
        ], $mergedMethods, $mergedProperties));
    }

    private function mergeArguments(array $selfArguments, array $otherArguments): array
    {
        /** @var mixed $argument */
        foreach ($otherArguments as $index => $argument) {
            /** @var mixed */
            $selfArguments[$index] = $argument;
        }

        return $selfArguments;
    }

    /**
     * @param mixed $value
     */
    private function getType($value): string
    {
        return is_object($value) ? get_class($value) : gettype($value);
    }
}
