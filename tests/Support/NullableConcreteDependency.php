<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Support;

final class NullableConcreteDependency
{
    public function __construct(?Car $car)
    {
    }
}
