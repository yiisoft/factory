<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Support;

final class Cube
{
    private ColorInterface $color;

    public function __construct(ColorInterface $color)
    {
        $this->color = $color;
    }

    public function getColor(): ColorInterface
    {
        return $this->color;
    }
}
