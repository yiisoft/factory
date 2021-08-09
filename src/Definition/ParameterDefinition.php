<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Definition;

use ReflectionParameter;
use Yiisoft\Factory\DependencyResolverInterface;
use Yiisoft\Factory\Exception\NotInstantiableScalarException;

final class ParameterDefinition implements DefinitionInterface
{
    private ReflectionParameter $parameter;
    private bool $hasValue = false;

    /**
     * @var mixed
     */
    private $value = null;

    public function __construct(ReflectionParameter $parameter)
    {
        $this->parameter = $parameter;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value): void
    {
        $this->hasValue = true;
        $this->value = $value;
    }

    public function hasValue(): bool
    {
        return $this->hasValue;
    }

    public function isVariadic(): bool
    {
        return $this->parameter->isVariadic();
    }

    public function isOptional(): bool
    {
        return $this->parameter->isOptional();
    }

    public function resolve(DependencyResolverInterface $container)
    {
        if (!$this->hasValue) {
            throw new NotInstantiableScalarException('XX');
        }

        return $this->value;
    }
}
