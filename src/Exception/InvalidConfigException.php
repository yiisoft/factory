<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Exception;

use Exception;
use Psr\Container\ContainerExceptionInterface;

/**
 * InvalidConfigException is thrown when configuration passed to
 * container is not valid.
 */
class InvalidConfigException extends Exception implements ContainerExceptionInterface
{
}
