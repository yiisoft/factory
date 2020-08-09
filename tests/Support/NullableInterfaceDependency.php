<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Support;

class NullableInterfaceDependency
{
    public function __construct(?EngineInterface $engine)
    {
    }
}
