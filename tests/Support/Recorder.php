<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Support;

final class Recorder
{
    private array $record = [];

    public function __call($name, $arguments)
    {
        $this->record[] = "Call $name()";
    }

    public function __isset($name)
    {
        return true;
    }

    public function __set($name, $value)
    {
        $this->record[] = "Set @$name";
    }

    public function __get($name)
    {
        return null;
    }

    public function getEvents(): array
    {
        return $this->record;
    }
}
