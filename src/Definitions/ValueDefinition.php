<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Definitions;

use Psr\Container\ContainerInterface;
use Yiisoft\Factory\FactoryInterface;

class ValueDefinition implements DefinitionInterface
{
    private $value;

    private ?string $type;

    public function __construct($value, string $type = null)
    {
        $this->value = $value;
        $this->type = $type;
    }

    public function getType(): string
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

    /**
     * This is used to detect circular reference.
     * If a concrete reference is guaranteed to never be part of such a circle
     * (for example because it references a simple value) NULL should be returned
     * @return string|null A string uniquely identifying a service in the container
     */
    public function getId(): ?string
    {
        return null;
    }
}
