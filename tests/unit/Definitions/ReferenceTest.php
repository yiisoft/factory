<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\di\tests\unit;

use PHPUnit\Framework\TestCase;
use yii\di\tests\support\EngineInterface;
use Yiisoft\Factory\Definitions\Reference;

/**
 * ReferenceTest contains tests for \yii\di\Reference
 */
class ReferenceTest extends TestCase
{
    public function testTo(): void
    {
        $ref = Reference::to(EngineInterface::class);
        $this->assertSame(EngineInterface::class, $ref->getId());
    }
}
