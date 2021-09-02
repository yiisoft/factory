<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Support;

final class InvokableCarFactory
{
    public function __invoke(EngineInterface $engine): Car
    {
        return new Car($engine);
    }
}
