<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Definition;

use Psr\Container\ContainerInterface;
use ReflectionParameter;
use Yiisoft\Factory\FactoryInterface;
use function is_object;

class ParameterDefinition implements DefinitionInterface
{
    private ReflectionParameter $parameter;
    private bool $hasValue = false;

    /**
     * @var mixed
     */
    private $value = null;
    private ?string $type;

    public function __construct(ReflectionParameter $parameter, string $type = null)
    {
        $this->parameter = $parameter;
        $this->type = $type;
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function resolve(ContainerInterface $container)
    {
        if ($container instanceof FactoryInterface && is_object($this->value)) {
            return clone $this->value;
        }

        return $this->value;
    }
}
