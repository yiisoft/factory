<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Support\Circular;

final class Egg
{
    public function __construct(Chicken $chicken) {}
}
