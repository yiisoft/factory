<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Definitions\Arrays;

use Psr\Container\ContainerInterface;
use Yiisoft\Factory\Definitions\DefinitionInterface;
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
    /**
     * @psalm-var class-string
     */
    private string $class;
    private array $constructorParameters;

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
        $this->setConstructorParameters($config);
        $this->setCalls($config);
        $this->setSetProperties($config);
    }

    /**
     * @throws InvalidConfigException
     */
    private function setClass(array $config): void
    {
        if (!array_key_exists(Key::CLASS_NAME, $config)) {
            throw new InvalidConfigException('Invalid definition: no class name specified.');
        }

        /** @var mixed */
        $class = $config[Key::CLASS_NAME];

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
    private function setConstructorParameters(array $config): void
    {
        $parameters = $config[Key::CONSTRUCTOR] ?? [];

        if (!is_array($parameters)) {
            throw new InvalidConfigException(
                sprintf(
                    'Invalid definition: incorrect constructor parameters. Expected array, got %s.',
                    $this->getType($parameters)
                )
            );
        }

        $this->constructorParameters = $parameters;
    }

    /**
     * @throws InvalidConfigException
     */
    private function setCalls(array $config): void
    {
        $items = $config[Key::CALL_METHODS] ?? [];

        if (!is_array($items)) {
            throw new InvalidConfigException(
                sprintf('Invalid definition: incorrect call methods. Expected array, got %s.', $this->getType($items))
            );
        }

        $callMethods = [];
        foreach ($items as $key => $value) {
            if (is_int($key)) {
                if (!is_string($value)) {
                    throw new InvalidConfigException(
                        sprintf('Invalid definition: expected method name, got %s', $this->getType($value))
                    );
                }
                if ($value === '') {
                    throw new InvalidConfigException('Invalid definition: expected method name, got empty string.');
                }
                $callMethods[$value] = [];
            } else {
                if (!is_array($value)) {
                    throw new InvalidConfigException('Invalid definition: incorrect call parameters.');
                }
                $callMethods[$key] = $value;
            }
        }

        $this->callMethods = $callMethods;
    }

    /**
     * @throws InvalidConfigException
     */
    private function setSetProperties(array $config): void
    {
        $properties = $config[Key::SET_PROPERTIES] ?? [];

        if (!is_array($properties)) {
            throw new InvalidConfigException('Invalid definition: incorrect properties.');
        }

        foreach ($properties as $key => $_value) {
            if (!is_string($key)) {
                throw new InvalidConfigException('Invalid definition: incorrect property name.');
            }
        }

        /** @psalm-var array<string,mixed> $properties */

        $this->setProperties = $properties;
    }

    /**
     * @psalm-return class-string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    public function getConstructorParameters(): array
    {
        return $this->constructorParameters;
    }

    /**
     * @psalm-return array<string, array>
     */
    public function getCallMethods(): array
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
        return Builder::getInstance()->build($container, $this);
    }

    public function merge(self $other): self
    {
        $callMethods = $this->getCallMethods();
        foreach ($other->getCallMethods() as $method => $parameters) {
            $callMethods[$method] = isset($callMethods[$method])
                ? $this->mergeParameters($callMethods[$method], $parameters)
                : $parameters;
        }

        return new self([
            Key::CLASS_NAME => $other->getClass(),
            Key::CONSTRUCTOR => $this->mergeParameters(
                $this->getConstructorParameters(),
                $other->getConstructorParameters()
            ),
            Key::CALL_METHODS => $callMethods,
            Key::SET_PROPERTIES => array_merge($this->getSetProperties(), $other->getSetProperties()),
        ]);
    }

    private function mergeParameters(array $selfParameters, array $otherParameters): array
    {
        foreach ($otherParameters as $index => $_param) {
            /** @var mixed */
            $selfParameters[$index] = $otherParameters[$index];
        }

        return $selfParameters;
    }

    /**
     * @param mixed $value
     */
    private function getType($value): string
    {
        return is_object($value) ? get_class($value) : gettype($value);
    }
}
