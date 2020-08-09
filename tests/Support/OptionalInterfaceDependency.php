<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Support;

class OptionalInterfaceDependency
{
    public function __construct(EngineInterface $engine = null)
    {
    }
}
