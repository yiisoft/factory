<?php
namespace Yiisoft\Factory\Exceptions;

use Psr\Container\ContainerExceptionInterface;

/**
 * InvalidConfigException is thrown when configuration passed to
 * container is not valid.
 */
class InvalidConfigException extends \Exception implements ContainerExceptionInterface
{
}
