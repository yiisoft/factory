<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Definition;

use Yiisoft\Factory\Exception\InvalidConfigException;
use function get_class;
use function gettype;
use function is_array;
use function is_object;
use function is_string;

final class ArrayDefinitionValidator
{
    /**
     * @param mixed $class
     *
     * @throws InvalidConfigException
     */
    public static function validateClassName($class): void
    {
        if (!is_string($class)) {
            throw new InvalidConfigException(sprintf('Invalid definition: invalid class name "%s".', (string)$class));
        }

        if ($class === '') {
            throw new InvalidConfigException('Invalid definition: empty class name.');
        }
    }

    /**
     * @param mixed $arguments
     *
     * @psalm-assert array $arguments
     *
     * @throws InvalidConfigException
     */
    public static function validateConstructorArguments($arguments): void
    {
        if (!is_array($arguments)) {
            throw new InvalidConfigException(
                sprintf(
                    'Invalid definition: incorrect constructor arguments. Expected array, got %s.',
                    self::getType($arguments)
                )
            );
        }
    }

    /**
     * @param mixed $arguments
     *
     * @psalm-assert array $arguments
     *
     * @throws InvalidConfigException
     */
    public static function validateMethodArguments($arguments): void
    {
        if (!is_array($arguments)) {
            throw new InvalidConfigException(
                sprintf('Invalid definition: incorrect method arguments. Expected array, got %s.', self::getType($arguments))
            );
        }
    }

    /**
     * @param mixed $value
     */
    private static function getType($value): string
    {
        return is_object($value) ? get_class($value) : gettype($value);
    }
}
