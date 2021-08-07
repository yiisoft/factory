<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Support;

use function func_get_args;

final class ExcessiveConstructorParameters
{
    private $parameter;

    private array $allParameters;

    public function __construct($parameter)
    {
        $this->parameter = $parameter;
        $this->allParameters = func_get_args();
    }

    /**
     * @return mixed
     */
    public function getParameter()
    {
        return $this->parameter;
    }

    public function getAllParameters(): array
    {
        return $this->allParameters;
    }
}
