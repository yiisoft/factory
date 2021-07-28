<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Support;

final class NullableInterfaceDependency
{
    public function __construct(?EngineInterface $engine)
    {
    }
}
