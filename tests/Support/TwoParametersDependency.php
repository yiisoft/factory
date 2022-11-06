<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Support;

final class TwoParametersDependency
{
    public function __construct(private string $firstParameter, private string $secondParameter)
    {
    }

    public function getFirstParameter(): string
    {
        return $this->firstParameter;
    }

    public function getSecondParameter(): string
    {
        return $this->secondParameter;
    }
}
