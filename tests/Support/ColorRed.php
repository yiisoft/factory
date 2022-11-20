<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Support;

/**
 * Class ColorRed
 */
final class ColorRed implements ColorInterface
{
    private const COLOR_RED = 'red';

    public function getColor(): string
    {
        return self::COLOR_RED;
    }
}
