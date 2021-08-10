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
}
