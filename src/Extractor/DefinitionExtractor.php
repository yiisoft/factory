<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Extractor;

use Closure;
use ReflectionClass;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;
use Yiisoft\Factory\Definition\ClassDefinition;
use Yiisoft\Factory\Definition\DefinitionInterface;
use Yiisoft\Factory\Definition\ParameterDefinition;
use Yiisoft\Factory\Exception\NotInstantiableException;

/**
 * Class DefinitionExtractor
 * This implementation resolves dependencies by using class type hints.
 * Note that service names need not match the parameter names, parameter names are ignored
 */
class DefinitionExtractor implements ExtractorInterface
{
    /**
     * @psalm-param class-string $class
     *
     * @return DefinitionInterface[]
     * @psalm-return array<string, DefinitionInterface>
     */
    public function fromClassName(string $class): array
    {
        $reflectionClass = new ReflectionClass($class);
        if (!$reflectionClass->isInstantiable()) {
            throw new NotInstantiableException($class);
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
        /**
         * @psalm-suppress UndefinedClass
         *
         * @var ReflectionNamedType|ReflectionUnionType|null $type
         */
        $type = $parameter->getType();

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
        $definition = new ParameterDefinition(
            $parameter,
            $type !== null ? $type->getName() : null
        );

        if ($parameter->isDefaultValueAvailable()) {
            $definition->setValue($parameter->getDefaultValue());
        } elseif (!$parameter->isVariadic()) {
            $definition->setValue(null);
        }

        return $definition;
    }

    /**
     * @return DefinitionInterface[]
     * @psalm-return array<string, DefinitionInterface>
     */
    public function fromCallable(callable $callable): array
    {
        return $this->fromFunction(new ReflectionFunction(Closure::fromCallable($callable)));
    }
}
