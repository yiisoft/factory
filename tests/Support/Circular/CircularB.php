<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Support\Circular;

final class CircularB
{
    public ?CircularA $a;

    public function __construct(?CircularA $a = null)
    {
        $this->a = $a;
    }
}
