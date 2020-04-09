<?php

namespace Yiisoft\Factory\Tests\Support;

class TwoParamsDependency
{
    private string $firstParam;

    private string $secondParam;

    public function __construct(string $firstParam, string $secondParam)
    {
        $this->firstParam = $firstParam;
        $this->secondParam = $secondParam;
    }

    public function getFirstParam(): string
    {
        return $this->firstParam;
    }

    public function getSecondParam(): string
    {
        return $this->secondParam;
    }
}
