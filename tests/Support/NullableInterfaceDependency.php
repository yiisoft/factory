<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Support;

final class NullableInterfaceDependency
{
    public function __construct(private ?\Yiisoft\Factory\Tests\Support\EngineInterface $engine)
    {
    }

    public function getEngine(): ?EngineInterface
    {
        return $this->engine;
    }
}
