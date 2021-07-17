<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Definition;

use Yiisoft\Factory\FactoryContainer;

use function is_object;

class ValueDefinition implements DefinitionInterface
{
    /**
     * @var mixed
     */
    private $value;

    private ?string $type;

    /**
     * @param mixed $value
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

    public function resolve(FactoryContainer $container)
    {
        if ($container->isUsedFactory() && is_object($this->value)) {
            return clone $this->value;
        }

        return $this->value;
    }
}
