<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Support\Circular;

final class CircularB
{
    public function __construct(public ?CircularA $a = null) {}
}
