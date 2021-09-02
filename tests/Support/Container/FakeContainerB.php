<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Support\Container;

use Psr\Container\ContainerInterface;

final class FakeContainerB implements ContainerInterface
{
    public function get(string $id)
    {
    }

    public function has(string $id)
    {
    }
}
