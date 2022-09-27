<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Support;

final class Firefighter
{
    public function __construct(private ?string $name)
    {
    }

    public function getName(): ?string
    {
        return $this->name;
    }
}
