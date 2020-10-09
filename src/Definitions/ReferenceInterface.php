<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Definitions;

interface ReferenceInterface extends DefinitionInterface
{
    /**
     * @param mixed $id
     */
    public static function to($id): ReferenceInterface;
}
