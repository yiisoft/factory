<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Support;

final class TwoParametersDependency
{
    private string $firstParameter;

    private string $secondParameter;

    public function __construct(string $firstParameter, string $secondParameter)
    {
        $this->firstParameter = $firstParameter;
        $this->secondParameter = $secondParameter;
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
