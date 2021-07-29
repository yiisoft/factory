<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Support;

use function call_user_func;

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
        return call_user_func($this->callback);
    }
}
