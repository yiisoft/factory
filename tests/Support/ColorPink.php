<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Support;

/**
 * Class ColorPink
 */
final class ColorPink implements ColorInterface
{
    private const COLOR_PINK = 'pink';

    public function getColor(): string
    {
        return static::COLOR_PINK;
    }
}
