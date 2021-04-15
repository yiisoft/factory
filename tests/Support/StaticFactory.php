<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Support;

class StaticFactory
{

    public static function create(): StdClass
    {
        return new StdClass();
    }
}

