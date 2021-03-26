<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Support;

class Car
{
    public ?ColorInterface $color = null;
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

    public function setColor(ColorInterface $color): self
    {
        $this->color = $color;
        return $this;
    }

    public function getColor(): ?ColorInterface
    {
        return $this->color;
    }
}
