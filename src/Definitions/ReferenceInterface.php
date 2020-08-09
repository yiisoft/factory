<?php

namespace Yiisoft\Factory\Definitions;

interface ReferenceInterface extends DefinitionInterface
{
    public static function to($id): ReferenceInterface;
}
