<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Support;

final class PinkCircle
{
    public function __construct(private ColorPink $color) {}

    public function getColor(): ColorPink
    {
        return $this->color;
    }
}
