<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Support;

class NullableConcreteDependency
{
    public function __construct(?Car $car)
    {
    }
}
