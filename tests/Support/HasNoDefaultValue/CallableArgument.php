<?php

namespace Yiisoft\Factory\Tests\Support\HasNoDefaultValue;

class CallableArgument
{
    public function __construct(callable $arg)
    {
    }
}
