<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Definitions;

use Psr\Container\ContainerInterface;

class TagDefinition implements DefinitionInterface
{
    private array $references;

    public function __construct(array $ids)
    {
        $this->references = $ids;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function resolve(ContainerInterface $container)
    {
        $res = [];
        foreach ($this->references as $id) {
            $res[] = $container->get($id);
        }

        return $res;
    }

    public function addReferenceTo(string $id): void
    {
        $this->references[$id] = $id;
    }
}
