<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Support;

final class UnionCar
{
    private NonExistingEngine|EngineMarkOne|EngineMarkTwo $engine;

    public function __construct(NonExistingEngine|EngineMarkOne|EngineMarkTwo $engine)
    {
        $this->engine = $engine;
    }

    public function getEngine(): NonExistingEngine|EngineMarkOne|EngineMarkTwo
    {
        return $this->engine;
    }

    public function getEngineName(): string
    {
        return $this->engine->getName();
    }
}
