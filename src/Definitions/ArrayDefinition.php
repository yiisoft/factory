<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Definitions;

use Psr\Container\ContainerInterface;
use Yiisoft\Factory\Exceptions\InvalidConfigException;
use Yiisoft\Factory\Exceptions\NotInstantiableException;

/**
 * Builds object by array config
 */
class ArrayDefinition implements DefinitionInterface
{
    private const CLASS_KEY = '__class';
    private const PARAMS_KEY = '__construct()';

    private static ?ArrayBuilder $builder = null;
    private string $class;
    private array $params;
    private array $config;

    public function __construct(string $class, array $params = [], array $config = [])
    {
        if (empty($class)) {
            throw new InvalidConfigException('class name not given');
        }
        $this->class  = $class;
        $this->params = $params;
        $this->config = $config;
    }

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
            throw new NotInstantiableException(var_export($config, true));
        }

        return new static($class, $params, $config);
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
        return new static(
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
