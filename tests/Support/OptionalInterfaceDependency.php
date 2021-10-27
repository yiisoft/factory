<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Support;

final class OptionalInterfaceDependency
{
    public function __construct(EngineInterface $engine = null)
    {
    }
}
