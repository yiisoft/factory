<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Support;

final class VariadicConstructor
{
    private array $parameters;

    public function __construct(private mixed $first, private EngineInterface $engine, mixed ...$parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @return mixed
     */
    public function getFirst()
    {
        return $this->first;
    }

    public function getEngine(): EngineInterface
    {
        return $this->engine;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }
}
