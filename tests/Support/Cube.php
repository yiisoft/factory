<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Support;

final class Cube
{
    public function __construct(private ColorInterface $color)
    {
    }

    public function getColor(): ColorInterface
    {
        return $this->color;
    }
}
