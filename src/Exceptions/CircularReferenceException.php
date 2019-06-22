<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace Yiisoft\Factory\Exceptions;

use Psr\Container\ContainerExceptionInterface;

/**
 * CircularReferenceException is thrown when DI configuration
 * contains self-references of any level and thus could not
 * be resolved.
 */
class CircularReferenceException extends \Exception implements ContainerExceptionInterface
{
}
