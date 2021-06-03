<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Support;

use Closure;

final class VariadicClosures
{
    private array $closures;

    public function __construct(Closure ...$closures)
    {
        $this->closures = $closures;
    }

    public function getClosures(): array
    {
        return $this->closures;
    }
}
