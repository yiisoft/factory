<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Definition;

use Yiisoft\Factory\Exception\InvalidConfigException;

use function get_class;
use function gettype;
use function is_array;
use function is_callable;
use function is_object;
use function is_string;

final class DefinitionValidator
{
    /**
     * @param mixed $definition
     *
     * @throws InvalidConfigException
     */
    public static function validate($definition, ?string $id = null): void
    {
        // Reference or ready object
        if (is_object($definition)) {
            return;
        }

        // Class
        if (is_string($definition) && $definition !== '') {
            return;
        }

        // Callable definition
        if (is_callable($definition, true)) {
            return;
        }

        // Array definition
        if (is_array($definition)) {
            self::validateArrayDefinition($definition, $id);
            return;
        }

        throw new InvalidConfigException('Invalid definition:' . var_export($definition, true));
    }

    /**
     * @throws InvalidConfigException
     */
    private static function validateArrayDefinition(array $definition, ?string $id): void
    {
        foreach ($definition as $key => $value) {
            if (!is_string($key)) {
                throw new InvalidConfigException(
                    sprintf(
                        'Invalid definition: invalid key in array definition. Allow only string keys, got %d.',
                        $key,
                    ),
                );
            }

            // Class
            if ($key === ArrayDefinition::CLASS_NAME) {
                if (!is_string($value)) {
                    throw new InvalidConfigException(
                        sprintf(
                            'Invalid definition: invalid class name. Expected string, got %s.',
                            self::getType($value),
                        ),
                    );
                }
                if ($value === '') {
                    throw new InvalidConfigException('Invalid definition: empty class name.');
                }
                continue;
            }

            // Constructor arguments
            if ($key === ArrayDefinition::CONSTRUCTOR) {
                if (!is_array($value)) {
                    throw new InvalidConfigException(
                        sprintf(
                            'Invalid definition: incorrect constructor arguments. Expected array, got %s.',
                            self::getType($value)
                        )
                    );
                }
                continue;
            }

            // Methods and properties
            if (substr($key, -2) === '()') {
                if (!is_array($value)) {
                    throw new InvalidConfigException(
                        sprintf(
                            'Invalid definition: incorrect method arguments. Expected array, got %s.',
                            self::getType($value)
                        )
                    );
                }
                continue;
            }
            if (strncmp($key, '$', 1) === 0) {
                continue;
            }

            throw new InvalidConfigException(
                sprintf(
                    'Invalid definition: key "%s" is not allowed. Did you mean "%s()" or "$%s"?',
                    $key,
                    $key,
                    $key
                )
            );
        }

        if ($id === null && !isset($definition[ArrayDefinition::CLASS_NAME])) {
            throw new InvalidConfigException('Invalid definition: not define class name.');
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
