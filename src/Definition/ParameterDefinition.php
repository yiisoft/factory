<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Definition;

use ReflectionParameter;
use Yiisoft\Factory\FactoryContainer;

use function is_object;

class ParameterDefinition implements DefinitionInterface
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

    public function getParameter(): ReflectionParameter
    {
        return $this->parameter;
    }

    public function hasValue(): bool
    {
        return $this->hasValue;
    }

    public function resolve(FactoryContainer $container)
    {
        if ($container->isUsedFactory() && is_object($this->value)) {
            return clone $this->value;
        }

        return $this->value;
    }
}
