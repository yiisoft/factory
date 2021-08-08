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
        if (is_object($definition) && self::isValidObject($definition)) {
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

        throw new InvalidConfigException('Invalid definition: ' . var_export($definition, true));
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
                /** @var mixed $argument */
                foreach ($value as $argument) {
                    if (is_object($argument) && !self::isValidObject($argument)) {
                        throw new InvalidConfigException(
                            'Only references are allowed in constructor arguments, a definition object was provided: ' .
                            var_export($argument, true)
                        );
                    }
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

            self::throwInvalidArrayDefinitionKey($key);
        }

        if ($id === null && !isset($definition[ArrayDefinition::CLASS_NAME])) {
            throw new InvalidConfigException('Invalid definition: no class name specified.');
        }
    }

    /**
     * @throws InvalidConfigException
     */
    private static function throwInvalidArrayDefinitionKey(string $key): void
    {
        $preparedKey = trim(strtr($key, [
            '()' => '',
            '$' => '',
        ]));

        if ($preparedKey === '' || !preg_match('/^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*$/', $preparedKey)) {
            throw new InvalidConfigException(
                sprintf('Invalid definition: key "%s" is not allowed.', $key)
            );
        }

        throw new InvalidConfigException(
            sprintf(
                'Invalid definition: key "%s" is not allowed. Did you mean "%s()" or "$%s"?',
                $key,
                $preparedKey,
                $preparedKey
            )
        );
    }

    /**
     * Deny DefinitionInterface, exclude ReferenceInterface
     */
    private static function isValidObject(object $value): bool
    {
        return !($value instanceof DefinitionInterface) || $value instanceof ReferenceInterface;
    }

    /**
     * @param mixed $value
     */
    private static function getType($value): string
    {
        return is_object($value) ? get_class($value) : gettype($value);
    }
}
