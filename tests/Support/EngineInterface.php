<?php

namespace Yiisoft\Factory\Tests\Support;

/**
 * EngineInterface defines car engine interface
 */
interface EngineInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param int $value
     */
    public function setNumber(int $value): void;

    /**
     * @return int
     */
    public function getNumber(): int;
}
