<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Support;

final class NullableInterfaceDependency
{
    private ?EngineInterface $engine;

    public function __construct(?EngineInterface $engine)
    {
        $this->engine = $engine;
    }

    public function getEngine(): ?EngineInterface
    {
        return $this->engine;
    }
}
