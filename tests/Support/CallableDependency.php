<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Support;

final class CallableDependency
{
    /**
     * @var callable
     */
    private $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * @return mixed
     */
    public function get()
    {
        return ($this->callback)();
    }
}
