<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Definitions;

use Psr\Container\ContainerInterface;
use Yiisoft\Factory\Exceptions\InvalidConfigException;

/**
 * Builds object by array config
 */
class ArrayDefinition implements DefinitionInterface
{
    private const CLASS_KEY = '__class';
    private const PARAMS_KEY = '__construct()';

    /**
     * @var class-string
     */
    private string $class;
    private array $params;
    private array $config;
    private static ?ArrayBuilder $builder = null;

    /**
     * @param class-string $class
     * @param array $params
     * @param array $config
     */
    public function __construct(string $class, array $params = [], array $config = [])
    {
        $this->class  = $class;
        $this->params = $params;
        $this->config = $config;
    }

    /**
     * @return class-string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public static function fromArray(string $class = null, array $params = [], array $config = []): self
    {
        $class  = $config[self::CLASS_KEY] ?? $class;
        $params = $config[self::PARAMS_KEY] ?? $params;

        unset($config[self::CLASS_KEY], $config[self::PARAMS_KEY]);

        if (empty($class)) {
            throw new InvalidConfigException('Invalid definition: empty string');
        }

        return new self($class, $params, $config);
    }

    public function resolve(ContainerInterface $container)
    {
        return $this->getBuilder()->build($container, $this);
    }

    private function getBuilder(): ArrayBuilder
    {
        if (static::$builder === null) {
            static::$builder = new ArrayBuilder();
        }

        return static::$builder;
    }

    public function merge(self $other): self
    {
        return new self(
            $other->class,
            $this->mergeParameters($this->params, $other->params),
            array_merge($this->config, $other->config)
        );
    }

    private function mergeParameters(array $selfParameters, array $otherParameters): array
    {
        foreach ($otherParameters as $index => $param) {
            $selfParameters[$index] = $otherParameters[$index];
        }

        return $selfParameters;
    }
}
