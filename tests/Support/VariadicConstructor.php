<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Support;

final class VariadicConstructor
{
    /**
     * @var mixed
     */
    private $first;

    private EngineInterface $engine;
    private array $parameters;

    /**
     * @param mixed $first
     * @param mixed ...$parameters
     */
    public function __construct($first, EngineInterface $engine, ...$parameters)
    {
        $this->first = $first;
        $this->engine = $engine;
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
