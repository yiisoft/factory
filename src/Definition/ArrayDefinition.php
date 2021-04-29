<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Definition;

use Psr\Container\ContainerInterface;
use Yiisoft\Factory\Exception\InvalidConfigException;

use Yiisoft\Factory\Exception\NotInstantiableException;

use function array_key_exists;
use function get_class;
use function gettype;
use function in_array;
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
     * @psalm-var array<string, mixed>
     */
    private array $methodsAndProperties;

    /**
     * @psalm-var array<string, mixed>
     */
    private array $meta;

    /**
     * @param array $config Container entry config.
     * @param bool $checkDefinition Check definition flag.
     * @param string[] $allowMeta
     *
     * @throws InvalidConfigException
     */
    public function __construct(array $config, array $allowMeta = [])
    {
        foreach ($config as $key => $_value) {
            if (!is_string($key)) {
                throw new InvalidConfigException('Invalid definition: keys should be string.');
            }
        }

        /** @psalm-var array<string, mixed> $config */

        $this->setClass($config);
        $this->setConstructorArguments($config);
        $this->setMethodsAndProperties($config);
        $this->setMeta($config, $allowMeta);
    }

    /**
     * @throws InvalidConfigException
     */
    private function setClass(array &$config): void
    {
        if (!array_key_exists(self::CLASS_NAME, $config)) {
            throw new InvalidConfigException('Invalid definition: no class name specified.');
        }

        /** @var mixed */
        $class = $config[self::CLASS_NAME];
        unset($config[self::CLASS_NAME]);

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
    private function setConstructorArguments(array &$config): void
    {
        if (!isset($config[self::CONSTRUCTOR])) {
            $this->constructorArguments = [];
            return;
        }

        $arguments = $config[self::CONSTRUCTOR];
        unset($config[self::CONSTRUCTOR]);

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
    private function setMethodsAndProperties(array &$config): void
    {
        $methodsAndProperties = [];

        foreach ($config as $key => $value) {
            if (substr($key, -2) === '()') {
                if (!is_array($value)) {
                    throw new InvalidConfigException(
                        sprintf('Invalid definition: incorrect method arguments. Expected array, got %s.', $this->getType($value))
                    );
                }

                /** @var string $methodName */
                $methodName = substr($key, 0, -2);

                $methodsAndProperties[] = [ArrayDefinitionBuilder::METHOD, $methodName, $value];
                unset($config[$key]);
            } elseif (strncmp($key, '$', 1) === 0) {
                /** @var string $propertyName */
                $propertyName = substr($key, 1);

                $methodsAndProperties[$key] = [ArrayDefinitionBuilder::PROPERTY, $propertyName, $value];
                unset($config[$key]);
            }
        }

        $this->methodsAndProperties = $methodsAndProperties;
    }

    /**
     * @throws InvalidConfigException
     */
    private function setMeta(array $config, array $allowMeta): void
    {
        foreach ($config as $key => $_value) {
            if (!in_array($key, $allowMeta, true)) {
                throw new InvalidConfigException(
                    sprintf(
                        'Invalid definition: metadata "%s" is not allowed. Did you mean "%s()" or "$%s"?',
                        $key,
                        $key,
                        $key
                    )
                );
            }
        }

        $this->meta = $config;
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
        $new = clone $this;
        $new->class = $other->class;
        $new->constructorArguments = $this->mergeArguments($this->constructorArguments, $other->constructorArguments);

        $methodsAndProperties = $this->methodsAndProperties;
        foreach ($other->methodsAndProperties as $key => $item) {
            if ($item[0] === ArrayDefinitionBuilder::PROPERTY) {
                $methodsAndProperties[$key] = $item;
            } elseif ($item[0] === ArrayDefinitionBuilder::METHOD) {
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
