<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Exception;

use Exception;

final class NotInstantiableClassException extends NotInstantiableException
{
    public function __construct(string $class, string $message = null, int $code = 0, Exception $previous = null)
    {
        if ($message === null) {
            $message = "Can not instantiate $class.";
        }
        parent::__construct($message, $code, $previous);
    }
}
