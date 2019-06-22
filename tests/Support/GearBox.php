<?php

namespace Yiisoft\Factory\Tests\Support;

/**
 * A gear box.
 */
class GearBox
{
    private $maxGear;

    public function __construct(int $maxGear = 5)
    {
        $this->maxGear = $maxGear;
    }
}
