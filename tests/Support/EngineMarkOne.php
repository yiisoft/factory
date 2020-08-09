<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Support;

/**
 * EngineMarkOne
 */
class EngineMarkOne implements EngineInterface
{
    public const NAME = 'Mark One';

    private int $number = 0;


    /**
     * @return string
     */
    public function getName(): string
    {
        return static::NAME;
    }

    /**
     * @param int $value
     */
    public function setNumber(int $value): void
    {
        $this->number = $value;
    }

    /**
     * @return int
     */
    public function getNumber(): int
    {
        return $this->number;
    }
}
