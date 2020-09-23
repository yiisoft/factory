<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Support;

use Yiisoft\Di\Tests\Support\ColorInterface;

class Car
{
    public ColorInterface $color;
    private EngineInterface $engine;

    public function __construct(EngineInterface $engine)
    {
        $this->engine = $engine;
    }

    public function getEngine(): EngineInterface
    {
        return $this->engine;
    }

    public function getEngineName(): string
    {
        return $this->engine->getName();
    }
}
