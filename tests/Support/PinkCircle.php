<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Support;

final class PinkCircle
{
    private ColorPink $color;

    public function __construct(ColorPink $color)
    {
        $this->color = $color;
    }

    public function getColor(): ColorPink
    {
        return $this->color;
    }
}
