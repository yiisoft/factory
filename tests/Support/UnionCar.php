<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Support;

use Yiisoft\Di\Tests\Support\ColorInterface;

class UnionCar
{
    public ColorInterface $color;
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
