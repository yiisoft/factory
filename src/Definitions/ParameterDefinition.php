<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Definitions;

use ReflectionParameter;

class ParameterDefinition extends ValueDefinition
{
    private ReflectionParameter $parameter;

    /**
     * @param mixed $value
     */
    public function __construct(ReflectionParameter $parameter, $value, string $type = null)
    {
        $this->parameter = $parameter;
        parent::__construct($value, $type);
    }

    public function getParameter(): ReflectionParameter
    {
        return $this->parameter;
    }
}
