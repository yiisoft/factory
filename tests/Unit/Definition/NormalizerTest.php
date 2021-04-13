<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Unit\Definition;

use PHPUnit\Framework\TestCase;
use Yiisoft\Factory\Definition\Normalizer;
use Yiisoft\Factory\Exception\InvalidConfigException;
use Yiisoft\Factory\Tests\Support\EngineMarkOne;
use Yiisoft\Factory\Tests\Support\Phone;

/**
 * NormalizerTest contains tests for Yiisoft\Factory\Definition\Normalizer
 */
class NormalizerTest extends TestCase
{
    public function testParseCallableDefinition(): void
    {
        $fn = static fn () => new EngineMarkOne();
        $definition = [
            'definition' => $fn,
            'tags' => ['one', 'two'],
        ];
        [$definition, $meta] = Normalizer::parse($definition, ['tags']);
        $this->assertSame($fn, $definition);
        $this->assertSame(['tags' => ['one', 'two']], $meta);
    }

    public function testParseArrayDefinition(): void
    {
        $definition = [
            'class' => EngineMarkOne::class,
            '__construct()' => [42],
            'tags' => ['one', 'two'],
        ];
        [$definition, $meta] = Normalizer::parse($definition, ['tags']);
        $this->assertSame(['class' => EngineMarkOne::class, '__construct()' => [42]], $definition);
        $this->assertSame(['tags' => ['one', 'two']], $meta);
    }

    public function testErrorOnMethodTypo(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('Invalid definition: metadata "setId" is not allowed. Did you mean "setId()" or "@setId"?');

        Normalizer::parse([
            'class' => Phone::class,
            'setId' => [42],
        ], []);
    }

    public function testErrorOnPropertyTypo(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('Invalid definition: metadata "dev" is not allowed. Did you mean "dev()" or "@dev"?');
        Normalizer::parse([
            'class' => Phone::class,
            'dev' => true,
        ], []);
    }
}
