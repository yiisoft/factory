<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Support\Circular;

final class CircularA
{
    public ?CircularB $b;

    public function __construct(?CircularB $b = null)
    {
        $this->b = $b;
    }
}
