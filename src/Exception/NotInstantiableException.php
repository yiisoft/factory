<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Exception;

use Exception;
use Psr\Container\ContainerExceptionInterface;

/**
 * NotInstantiableException represents an exception caused by incorrect dependency injection container
 * configuration or usage.
 */
class NotInstantiableException extends Exception implements ContainerExceptionInterface
{
    public function __construct(string $class, string $message = null, int $code = 0, Exception $previous = null)
    {
        if ($message === null) {
            $message = "Can not instantiate $class.";
        }
        parent::__construct($message, $code, $previous);
    }
}
