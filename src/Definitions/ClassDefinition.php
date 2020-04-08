<?php

namespace Yiisoft\Factory\Definitions;

use Psr\Container\ContainerInterface;
use Yiisoft\Factory\Exceptions\InvalidConfigException;

/**
 * Reference points to a class name in the container
 */
class ClassDefinition implements DefinitionInterface
{
    private $class;

    private $optional;

    /**
     * Constructor.
     * @param string $class the class name
     * @param bool $optional if null should be returned instead of throwing an exception
     */
    public function __construct(string $class, bool $optional)
    {
        $this->class = $class;
        $this->optional = $optional;
    }

    public function getType(): string
    {
        return $this->class;
    }

    public function resolve(ContainerInterface $container)
    {
        try {
            $result = $container->get($this->class);
        } catch (\Throwable $t) {
            if ($this->optional) {
                return null;
            }
            throw $t;
        }

        if (!$result instanceof $this->class) {
            throw new InvalidConfigException('Container returned incorrect type for service ' . $this->class);
        }
        return $result;
    }
}
