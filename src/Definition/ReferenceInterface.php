<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Definition;

interface ReferenceInterface extends DefinitionInterface
{
    /**
     * @param mixed $id
     */
    public static function to($id): self;
}
