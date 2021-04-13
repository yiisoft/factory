<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Definition;

use Psr\Container\ContainerInterface;
use Throwable;
use Yiisoft\Factory\Exception\InvalidConfigException;

use function gettype;

/**
 * Reference points to a class name in the container
 */
class ClassDefinition implements DefinitionInterface
{
    private string $class;
    private bool $optional;

    /**
     * Constructor.
     *
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
        if ($this->isUnionType()) {
            return $this->resolveUnionType($container);
        }

        try {
            /** @var mixed */
            $result = $container->get($this->class);
        } catch (Throwable $t) {
            if ($this->optional) {
                return null;
            }
            throw $t;
        }

        if (!$result instanceof $this->class) {
            $actualType = gettype($this->class);
            throw new InvalidConfigException(
                "Container returned incorrect type \"$actualType\" for service \"$this->class\"."
            );
        }
        return $result;
    }

    /**
     * @throws Throwable
     *
     * @return mixed
     */
    private function resolveUnionType(ContainerInterface $container)
    {
        $types = explode('|', $this->class);

        foreach ($types as $type) {
            try {
                /** @var mixed */
                $result = $container->get($type);
                if (!$result instanceof $type) {
                    $actualType = gettype($this->class);
                    throw new InvalidConfigException(
                        "Container returned incorrect type \"$actualType\" for service \"$this->class\"."
                    );
                }
                return $result;
            } catch (Throwable $t) {
                $error = $t;
            }
        }

        if ($this->optional) {
            return null;
        }

        throw $error;
    }

    private function isUnionType(): bool
    {
        return strpos($this->class, '|') !== false;
    }
}
