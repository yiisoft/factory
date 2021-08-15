<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Definition;

use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;
use Yiisoft\Factory\DependencyResolverInterface;
use Yiisoft\Factory\Exception\NotInstantiableException;

final class ParameterDefinition implements DefinitionInterface
{
    private ReflectionParameter $parameter;

    public function __construct(ReflectionParameter $parameter)
    {
        $this->parameter = $parameter;
    }

    public function isVariadic(): bool
    {
        return $this->parameter->isVariadic();
    }

    public function isOptional(): bool
    {
        return $this->parameter->isOptional();
    }

    public function hasValue(): bool
    {
        return $this->parameter->isDefaultValueAvailable() || $this->parameter->allowsNull();
    }

    public function resolve(DependencyResolverInterface $container)
    {
        if ($this->parameter->isDefaultValueAvailable()) {
            return $this->parameter->getDefaultValue();
        }

        if ($this->parameter->allowsNull()) {
            return null;
        }

        if ($this->isOptional()) {
            throw new NotInstantiableException(
                sprintf(
                    'Can not determine default value of parameter "%s" when instantiating "%s" ' .
                    'because it is PHP internal. Please specify argument explicitly.',
                    $this->parameter->getName(),
                    $this->getCallable(),
                )
            );
        }

        throw new NotInstantiableException(
            sprintf(
                'Can not determine value of the "%s" parameter of type "%s" when instantiating "%s". ' .
                'Please specify argument explicitly.',
                $this->parameter->getName(),
                $this->getType(),
                $this->getCallable(),
            )
        );
    }

    private function getType(): string
    {
        $type = $this->parameter->getType();

        if ($type === null) {
            return 'undefined';
        }

        /** @psalm-suppress UndefinedClass, TypeDoesNotContainType */
        if ($type instanceof ReflectionUnionType) {
            /** @var ReflectionNamedType[] */
            $namedTypes = $type->getTypes();
            $names = array_map(
                static fn (ReflectionNamedType $t) => $t->getName(),
                $namedTypes
            );
            return implode('|', $names);
        }

        /** @var ReflectionNamedType $type */

        return $type->getName();
    }

    private function getCallable(): string
    {
        $callable = [];

        $class = $this->parameter->getDeclaringClass();
        if ($class !== null) {
            $callable[] = $class->getName();
        }
        $callable[] = $this->parameter->getDeclaringFunction()->getName() . '()';

        return implode('::', $callable);
    }
}
