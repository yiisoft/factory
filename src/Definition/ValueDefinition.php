<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Definition;

use Yiisoft\Factory\DependencyResolverInterface;

final class ValueDefinition implements DefinitionInterface
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

    public function resolve(DependencyResolverInterface $container)
    {
        return $this->value;
    }
}
