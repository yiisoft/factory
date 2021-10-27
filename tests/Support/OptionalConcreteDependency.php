<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Support;

final class OptionalConcreteDependency
{
    public function __construct(Car $car = null)
    {
    }
}
