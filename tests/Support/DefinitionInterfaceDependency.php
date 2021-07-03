<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Support;

use Yiisoft\Factory\Definition\DefinitionInterface;

final class DefinitionInterfaceDependency
{
    public function __construct(DefinitionInterface $object)
    {
    }
}
