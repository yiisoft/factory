<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Support;

final class SelfType
{
    private ?string $color = null;

    public function __construct(?self $source = null)
    {
        if ($source !== null) {
            $this->color = $source->getColor();
        }
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): void
    {
        $this->color = $color;
    }
}
