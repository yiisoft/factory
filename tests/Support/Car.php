<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Support;

use Yiisoft\Injector\Tests\Support\ColorInterface;

/**
 * A car
 */
class Car
{
    /**
     * @var ColorInterface
     */
    public $color;

    /**
     * @var EngineInterface
     */
    private $engine;

    /**
     * Car constructor.
     * @param EngineInterface $engine
     */
    public function __construct(EngineInterface $engine)
    {
        $this->engine = $engine;
    }

    /**
     * @return EngineInterface
     */
    public function getEngine(): EngineInterface
    {
        return $this->engine;
    }

    /**
     * @return string
     */
    public function getEngineName(): string
    {
        return $this->engine->getName();
    }
}
