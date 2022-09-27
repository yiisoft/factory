<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Support;

final class MethodTest
{
    private $value;

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    public function setValue(mixed $value): void
    {
        $this->value = $value;
    }
}
