<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Support\Circular;

final class Chicken
{
    public function __construct(Egg $egg)
    {
    }
}
