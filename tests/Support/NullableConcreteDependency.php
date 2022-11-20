<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Support;

final class NullableConcreteDependency
{
    public function __construct(private ?Car $car)
    {
    }

    public function getCar(): Car
    {
        return $this->car;
    }
}
