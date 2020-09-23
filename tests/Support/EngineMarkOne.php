<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Support;

class EngineMarkOne implements EngineInterface
{
    public const NAME = 'Mark One';

    private int $number = 0;

    public function getName(): string
    {
        return static::NAME;
    }

    public function setNumber(int $value): void
    {
        $this->number = $value;
    }

    public function getNumber(): int
    {
        return $this->number;
    }
}
