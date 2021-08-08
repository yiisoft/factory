<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Support\Circular;

final class TreeItem
{
    public function __construct(self $treeItem)
    {
    }
}
