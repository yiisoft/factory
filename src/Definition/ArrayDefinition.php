<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Definition;

use Psr\Container\ContainerInterface;
use Yiisoft\Factory\Exception\InvalidConfigException;

use Yiisoft\Factory\Exception\NotInstantiableException;

use function array_key_exists;
use function get_class;
use function gettype;
use function is_array;
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
     * @psalm-var array<string, mixed|array>
     */
    private array $methodsAndProperties;

    /**
     * @psalm-var array<string, mixed>
     */
    private array $meta = [];

    /**
     * @param array $config Container entry config.
     * @param bool $checkDefinition Check definition flag.
     *
     * @throws InvalidConfigException
     */
    public function __construct(array $config, bool $checkDefinition = true)
    {
        if ($checkDefinition) {
            [$config,] = Normalizer::parse($config, []);
        }
        $this->setClass($config);
        unset($config[self::CLASS_NAME]);
        $this->setConstructorArguments($config);
        unset($config[self::CONSTRUCTOR]);
        $this->methodsAndProperties = $config;
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
        $methodsAndProperties = $this->getMethodsAndProperties();

        foreach ($other->getMethodsAndProperties() as $name => $arguments) {
            $methodsAndProperties[$name] = isset($methodsAndProperties[$name])
                ? $this->mergeArguments($methodsAndProperties[$name], $arguments)
                : $arguments;
        }

        return new self(array_merge([
            self::CLASS_NAME => $other->getClass(),
            self::CONSTRUCTOR => $this->mergeArguments(
                $this->getConstructorArguments(),
                $other->getConstructorArguments()
            ),
        ], $methodsAndProperties));
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

    /**
     * @param mixed $value
     */
    private function getType($value): string
    {
        return is_object($value) ? get_class($value) : gettype($value);
    }

    public function getMeta(): array
    {
        return $this->meta;
    }
}
