<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Support;

final class UnionBuiltInTypes
{
    public function __construct(string|int $values)
    {
    }
}
