<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Definition;

use ReflectionClass;
use ReflectionException;
use ReflectionFunctionAbstract;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;
use Yiisoft\Factory\Exception\NotFoundException;
use Yiisoft\Factory\Exception\NotInstantiableClassException;
use Yiisoft\Factory\Exception\NotInstantiableException;

/**
 * This class resolves dependencies by using class type hints.
 * Note that service names need not match the parameter names, parameter names are ignored
 *
 * @internal
 */
final class DefinitionExtractor
{
    private static ?self $instance = null;

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
     * @psalm-param class-string $class
     *
     * @throws NotFoundException
     * @throws NotInstantiableException
     *
     * @return DefinitionInterface[]
     * @psalm-return array<string, DefinitionInterface>
     */
    public function fromClassName(string $class): array
    {
        try {
            $reflectionClass = new ReflectionClass($class);
        } catch (ReflectionException $e) {
            throw new NotFoundException($class);
        }

        if (!$reflectionClass->isInstantiable()) {
            throw new NotInstantiableClassException($class);
        }

        $constructor = $reflectionClass->getConstructor();
        return $constructor === null ? [] : $this->fromFunction($constructor);
    }

    /**
     * @return DefinitionInterface[]
     * @psalm-return array<string, DefinitionInterface>
     */
    private function fromFunction(ReflectionFunctionAbstract $reflectionFunction): array
    {
        $result = [];
        foreach ($reflectionFunction->getParameters() as $parameter) {
            $result[$parameter->getName()] = $this->fromParameter($parameter);
        }
        return $result;
    }

    private function fromParameter(ReflectionParameter $parameter): DefinitionInterface
    {
        $type = $parameter->getType();

        if ($parameter->isVariadic()) {
            return $this->createParameterDefinition($parameter);
        }

        // PHP 8 union type is used as type hint
        /** @psalm-suppress UndefinedClass, TypeDoesNotContainType */
        if ($type instanceof ReflectionUnionType) {
            $types = [];
            /** @var ReflectionNamedType $unionType */
            foreach ($type->getTypes() as $unionType) {
                if (!$unionType->isBuiltin()) {
                    $typeName = $unionType->getName();
                    if ($typeName === 'self') {
                        // If type name is "self", it means that called class and
                        // $parameter->getDeclaringClass() returned instance of `ReflectionClass`.
                        /** @psalm-suppress PossiblyNullReference */
                        $typeName = $parameter->getDeclaringClass()->getName();
                    }

                    $types[] = $typeName;
                }
            }

            /** @psalm-suppress MixedArgument */
            return new ClassDefinition(implode('|', $types), $type->allowsNull());
        }

        /** @var ReflectionNamedType|null $type */

        // Our parameter has a class type hint
        if ($type !== null && !$type->isBuiltin()) {
            $typeName = $type->getName();
            if ($typeName === 'self') {
                // If type name is "self", it means that called class and
                // $parameter->getDeclaringClass() returned instance of `ReflectionClass`.
                /** @psalm-suppress PossiblyNullReference */
                $typeName = $parameter->getDeclaringClass()->getName();
            }

            return new ClassDefinition($typeName, $type->allowsNull());
        }

        // Our parameter does not have a class type hint and either has a default value or is nullable.
        return $this->createParameterDefinition($parameter);
    }

    private function createParameterDefinition(ReflectionParameter $parameter): ParameterDefinition
    {
        $definition = new ParameterDefinition($parameter);

        if ($parameter->isDefaultValueAvailable()) {
            $definition->setValue($parameter->getDefaultValue());
        } elseif (!$parameter->isVariadic() && $parameter->allowsNull()) {
            $definition->setValue(null);
        }

        return $definition;
    }
}
