<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Definitions;

use Psr\Container\ContainerInterface;
use Yiisoft\Factory\Exceptions\InvalidConfigException;

use Yiisoft\Factory\Exceptions\NotInstantiableException;

use function array_key_exists;
use function is_array;
use function is_int;
use function is_string;

/**
 * Builds object by array config
 */
class ArrayDefinition implements DefinitionInterface
{
    private const CLASS_KEY = 'class';
    private const CONSTRUCTOR_PARAMETERS_KEY = 'constructor';
    private const CALLS_KEY = 'calls';
    private const PROPERTIES_KEY = 'properties';

    /**
     * @psalm-var class-string
     */
    private string $class;
    private array $constructorParameters;

    /**
     * @psalm-var array<string, array>
     */
    private array $calls;

    /**
     * @psalm-var array<string, mixed>
     */
    private array $properties;

    /**
     * @psalm-param array{
     *   class: class-string,
     *   constructor?: array,
     *   calls?: array,
     *   properties?: array,
     * } $config
     *
     * @throws InvalidConfigException
     */
    public function __construct(array $config)
    {
        $this->setClass($config);
        $this->setConstructorParameters($config);
        $this->setCalls($config);
        $this->setProperties($config);
    }

    /**
     * @throws InvalidConfigException
     */
    private function setClass(array $config): void
    {
        if (!array_key_exists(self::CLASS_KEY, $config)) {
            throw new InvalidConfigException('Invalid definition: don\'t set class name.');
        }

        /** @var mixed */
        $class = $config[self::CLASS_KEY];

        if (!is_string($class)) {
            throw new InvalidConfigException('Invalid definition: invalid class name.');
        }

        if ($class === '') {
            throw new InvalidConfigException('Invalid definition: empty class name.');
        }

        if (!class_exists($class)) {
            throw new InvalidConfigException('Invalid definition: not exists class.');
        }

        $this->class = $class;
    }

    /**
     * @throws InvalidConfigException
     */
    private function setConstructorParameters(array $config): void
    {
        $parameters = $config[self::CONSTRUCTOR_PARAMETERS_KEY] ?? [];

        if (!is_array($parameters)) {
            throw new InvalidConfigException('Invalid definition: incorrect constructor parameters.');
        }

        $this->constructorParameters = $parameters;
    }

    /**
     * @throws InvalidConfigException
     */
    private function setCalls(array $config): void
    {
        $items = $config[self::CALLS_KEY] ?? [];

        if (!is_array($items)) {
            throw new InvalidConfigException('Invalid definition: incorrect calls.');
        }

        $calls = [];
        foreach ($items as $key => $value) {
            if (is_int($key)) {
                if (!is_string($value)) {
                    throw new InvalidConfigException('Invalid definition: incorrect call method.');
                }
                if ($value === '') {
                    throw new InvalidConfigException('Invalid definition: empty call method.');
                }
                $calls[$value] = [];
            } else {
                if (!is_array($value)) {
                    throw new InvalidConfigException('Invalid definition: incorrect call parameters.');
                }
                $calls[$key] = $value;
            }
        }

        $this->calls = $calls;
    }

    /**
     * @throws InvalidConfigException
     */
    private function setProperties(array $config): void
    {
        $properties = $config[self::PROPERTIES_KEY] ?? [];

        if (!is_array($properties)) {
            throw new InvalidConfigException('Invalid definition: incorrect properties.');
        }

        foreach ($properties as $key => $_value) {
            if (!is_string($key)) {
                throw new InvalidConfigException('Invalid definition: incorrect property name.');
            }
        }

        /** @psalm-var array<string,mixed> $properties */

        $this->properties = $properties;
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
    public function getCalls(): array
    {
        return $this->calls;
    }

    /**
     * @psalm-return array<string, mixed>
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @throws NotInstantiableException
     * @throws InvalidConfigException
     */
    public function resolve(ContainerInterface $container): object
    {
        return ArrayBuilder::getInstance()->build($container, $this);
    }

    public function merge(self $other): self
    {
        $calls = $this->getCalls();
        foreach ($other->getCalls() as $method => $parameters) {
            $calls[$method] = isset($calls[$method])
                ? $this->mergeParameters($calls[$method], $parameters)
                : $parameters;
        }

        return new self([
            self::CLASS_KEY => $other->getClass(),
            self::CONSTRUCTOR_PARAMETERS_KEY => $this->mergeParameters($this->getConstructorParameters(), $other->getConstructorParameters()),
            self::CALLS_KEY => $calls,
            self::PROPERTIES_KEY => array_merge($this->getProperties(), $other->getProperties()),
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
}
