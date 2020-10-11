<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Definitions;

use Psr\Container\ContainerInterface;
use Yiisoft\Factory\FactoryInterface;

class ValueDefinition implements DefinitionInterface
{
    /**
     * @var mixed $value
     */
    private $value;

    private ?string $type;

    /**
     * @param mixed $value
     * @param string $type
     */
    public function __construct($value, string $type = null)
    {
        $this->value = $value;
        $this->type = $type;
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
