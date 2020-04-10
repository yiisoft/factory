<?php

namespace Yiisoft\Factory\Tests\Support;

class TwoParametersDependency
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
