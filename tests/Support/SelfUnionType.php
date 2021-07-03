<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Support;

final class SelfUnionType
{
    private string $color;

    public function __construct(ColorInterface|self $source)
    {
        $this->color = $source->getColor();
    }

    public function getColor(): string
    {
        return $this->color;
    }
}
