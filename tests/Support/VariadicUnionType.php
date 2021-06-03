<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Support;

final class VariadicUnionType
{
    private array $items;

    public function __construct(int|string ...$closures)
    {
        $this->items = $closures;
    }

    public function getItems(): array
    {
        return $this->items;
    }
}
