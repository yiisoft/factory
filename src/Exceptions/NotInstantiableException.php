<?php

namespace Yiisoft\Factory\Exceptions;

use Psr\Container\ContainerExceptionInterface;

/**
 * NotInstantiableException represents an exception caused by incorrect dependency injection container
 * configuration or usage.
 */
class NotInstantiableException extends \Exception implements ContainerExceptionInterface
{
    /**
     * {@inheritdoc}
     */
    public function __construct($class, $message = null, $code = 0, \Exception $previous = null)
    {
        if ($message === null) {
            $message = "Can not instantiate $class.";
        }
        parent::__construct($message, $code, $previous);
    }
}
