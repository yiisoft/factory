<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Support;

final class NullableConcreteDependency
{
    private Car $car;

    public function __construct(?Car $car)
    {
        $this->car = $car;
    }

    public function getCar(): Car
    {
        return $this->car;
    }
}
