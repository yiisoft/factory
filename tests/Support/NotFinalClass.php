<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Support;

class NotFinalClass
{
    private $arguments;

    public function __construct(...$arguments)
    {
        $this->arguments = $arguments;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }
}
