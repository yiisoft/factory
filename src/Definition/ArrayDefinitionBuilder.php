<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Definition;

use Psr\Container\ContainerInterface;
use Yiisoft\Factory\Exception\InvalidConfigException;
use Yiisoft\Factory\Exception\NotFoundException;
use Yiisoft\Factory\Exception\NotInstantiableException;

use function array_key_exists;
use function call_user_func_array;
use function is_string;

/**
 * @internal Builds object by ArrayDefinition.
 */
final class ArrayDefinitionBuilder
{
    private static ?self $instance = null;

    /**
     * @psalm-var array<string, array<string, DefinitionInterface>>
     */
    private static array $dependencies = [];

    private function __construct()
    {
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @throws NotFoundException
     * @throws NotInstantiableException
     * @throws InvalidConfigException
     */
    public function build(ContainerInterface $container, ArrayDefinition $definition): object
    {
        $class = $definition->getClass();
        $dependencies = $this->getDependencies($class);
        $constructorArguments = $definition->getConstructorArguments();

        $this->injectArguments($dependencies, $constructorArguments);

        $resolved = DefinitionResolver::resolveArray($container, $dependencies);

        /** @psalm-suppress MixedMethodCall */
        $object = new $class(...array_values($resolved));

        $methodsAndProperties = $definition->getMethodsAndProperties();
        foreach ($methodsAndProperties as $item) {
            /** @var mixed $value */
            [$type, $name, $value] = $item;
            /** @var mixed */
            $value = DefinitionResolver::resolve($container, $value);
            if ($type === ArrayDefinition::TYPE_METHOD) {
                /** @var mixed */
                $setter = call_user_func_array([$object, $name], $value);
                if ($setter instanceof $object) {
                    /** @var object */
                    $object = $setter;
                }
            } elseif ($type === ArrayDefinition::TYPE_PROPERTY) {
                $object->$name = $value;
            }
        }

        return $object;
    }

    /**
     * @psalm-param array<string, DefinitionInterface> $dependencies
     *
     * @throws InvalidConfigException
     */
    private function injectArguments(array &$dependencies, array $arguments): void
    {
        $isIntegerIndexed = $this->isIntegerIndexed($arguments);
        $dependencyIndex = 0;
        $usedArguments = [];
        $isVariadic = false;
        foreach ($dependencies as $key => &$value) {
            if ($value instanceof ParameterDefinition && $value->getParameter()->isVariadic()) {
                $isVariadic = true;
            }
            $index = $isIntegerIndexed ? $dependencyIndex : $key;
            if (array_key_exists($index, $arguments)) {
                $value = DefinitionResolver::ensureResolvable($arguments[$index]);
                $usedArguments[$index] = 1;
            }
            $dependencyIndex++;
        }
        unset($value);
        if ($isVariadic) {
            /** @var mixed $value */
            foreach ($arguments as $index => $value) {
                if (!isset($usedArguments[$index])) {
                    $dependencies[$index] = DefinitionResolver::ensureResolvable($value);
                }
            }
        }
        /** @psalm-var array<string, DefinitionInterface> $dependencies */
    }

    /**
     * @throws InvalidConfigException
     */
    private function isIntegerIndexed(array $arguments): bool
    {
        $hasStringIndex = false;
        $hasIntegerIndex = false;

        foreach ($arguments as $index => $_argument) {
            if (is_string($index)) {
                $hasStringIndex = true;
                if ($hasIntegerIndex) {
                    break;
                }
            } else {
                $hasIntegerIndex = true;
                if ($hasStringIndex) {
                    break;
                }
            }
        }
        if ($hasIntegerIndex && $hasStringIndex) {
            throw new InvalidConfigException(
                'Arguments indexed both by name and by position are not allowed in the same array.'
            );
        }

        return $hasIntegerIndex;
    }

    /**
     * Returns the dependencies of the specified class.
     *
     * @param class-string $class Class name or interface name.
     *
     * @throws NotInstantiableException
     * @throws NotFoundException
     * @throws NotInstantiableException
     *
     * @return DefinitionInterface[] The dependencies of the specified class.
     * @psalm-return array<string, DefinitionInterface>
     */
    private function getDependencies(string $class): array
    {
        if (!isset(self::$dependencies[$class])) {
            self::$dependencies[$class] = DefinitionExtractor::getInstance()->fromClassName($class);
        }

        return self::$dependencies[$class];
    }
}
