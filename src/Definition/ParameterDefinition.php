<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Definition;

use ReflectionParameter;
use Yiisoft\Factory\DependencyResolverInterface;
use Yiisoft\Factory\Exception\NotInstantiableException;
use Yiisoft\Factory\Exception\NotDetermineDefaultValueOfPhpInternalException;

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
            throw new NotDetermineDefaultValueOfPhpInternalException($this->parameter);
        }

        throw new NotInstantiableException('Parameter definition does not contain a value.');
    }
}
