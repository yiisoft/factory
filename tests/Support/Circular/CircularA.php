<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Support\Circular;

final class CircularA
{
    public function __construct(public ?CircularB $b = null)
    {
    }
}
