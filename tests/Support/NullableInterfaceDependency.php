<?php


namespace Yiisoft\Factory\Tests\Support;

class NullableInterfaceDependency
{
    public function __construct(?EngineInterface $engine)
    {
    }
}
