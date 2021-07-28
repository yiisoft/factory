<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Definition;

use Yiisoft\Factory\Exception\InvalidConfigException;

interface ReferenceInterface extends DefinitionInterface
{
    /**
     * @param mixed $id
     *
     * @throws InvalidConfigException
     */
    public static function to($id): self;
}
