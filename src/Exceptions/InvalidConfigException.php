<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace Yiisoft\Factory\Exceptions;

use Psr\Container\ContainerExceptionInterface;

/**
 * InvalidConfigException is thrown when configuration passed to
 * container is not valid.
 */
class InvalidConfigException extends \Exception implements ContainerExceptionInterface
{
}
