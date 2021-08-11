<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Exception;

use ReflectionParameter;

final class NotDetermineDefaultValueOfPhpInternalException extends NotInstantiableException
{
    public function __construct(ReflectionParameter $parameter)
    {
        parent::__construct(
            sprintf(
                'Can not determine default value of parameter "%s" when instantinate "%s" ' .
                'because it is PHP internal. Please specify argument explicitly.',
                $parameter->getName(),
                $this->getCallable($parameter),
            )
        );
    }

    private function getCallable(ReflectionParameter $parameter): string
    {
        $callable = [];

        $class = $parameter->getDeclaringClass();
        if ($class !== null) {
            $callable[] = $class->getName();
        }
        $callable[] = $parameter->getDeclaringFunction()->getName() . '()';

        return implode('::', $callable);
    }
}
