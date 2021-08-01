<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Support;

final class CarFactory
{
    public function createWithColor(ColorInterface $color): Car
    {
        $car = new Car(new EngineMarkOne());
        return $car->setColor($color);
    }

    public static function create(EngineInterface $engine): Car
    {
        return new Car($engine);
    }
}
