<?php


namespace Yiisoft\Factory\Tests\Support;

class NullableConcreteDependency
{
    public function __construct(?Car $car)
    {
    }
}
