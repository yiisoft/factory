<?php
namespace Yiisoft\Factory\Definitions;

use Psr\Container\ContainerInterface;
use Yiisoft\Factory\Exceptions\InvalidConfigException;
use Yiisoft\Factory\Exceptions\NotInstantiableException;

/**
 * Builds object by array config
 */
class ArrayDefinition implements DefinitionInterface
{
    private $class;
    private $params;
    private $config;

    public function __construct(string $class, array $params = [], array $config = [])
    {
        if (empty($class)) {
            throw new InvalidConfigException('class name not given');
        }
        $this->class  = $class;
        $this->params = $params;
        $this->config = $config;
    }

    /**
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    private const CLASS_KEY = '__class';
    private const PARAMS_KEY = '__construct()';

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

    public function resolve(ContainerInterface $container, array $params = [])
    {
        if (!empty($params)) {
            $this->params = array_merge($this->params, $params);
        }

        return $this->getBuilder()->build($container, $this);
    }

    private static $builder;

    private function getBuilder(): ArrayBuilder
    {
        if (static::$builder === null) {
            static::$builder = new ArrayBuilder();
        }

        return static::$builder;
    }
}
