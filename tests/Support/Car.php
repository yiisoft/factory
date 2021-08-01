<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Support;

final class Car
{
    public ?ColorInterface $color = null;
    private EngineInterface $engine;
    private array $moreEngines;

    public function __construct(EngineInterface $engine, array $moreEngines = [])
    {
        $this->engine = $engine;
        $this->moreEngines = $moreEngines;
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

    public function getMoreEngines(): array
    {
        return $this->moreEngines;
    }
}
