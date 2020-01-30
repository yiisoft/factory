<?php

namespace Yiisoft\Factory\Tests\Support;

class OptionalConcreteDependency
{
    public function __construct(Car $car = null)
    {
    }
}
