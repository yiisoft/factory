<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Definitions;

use Psr\Container\ContainerInterface;
use Yiisoft\Factory\Exceptions\InvalidConfigException;
use Yiisoft\Factory\Exceptions\NotInstantiableException;
use Yiisoft\Factory\Extractors\DefinitionExtractor;

use function array_key_exists;
use function call_user_func_array;
use function is_string;

/**
 * Builds object by ArrayDefinition.
 */
class ArrayBuilder
{
    private static ?DefinitionExtractor $extractor = null;

    /**
     * @psalm-var array<string, array<string, DefinitionInterface>>
     */
    private static array $dependencies = [];

    /**
     * @throws NotInstantiableException
     * @throws InvalidConfigException
     */
    public function build(ContainerInterface $container, ArrayDefinition $definition): object
    {
        $class = $definition->getClass();
        $dependencies = $this->getDependencies($class);
        $parameters = $definition->getParams();
        $this->injectParameters($dependencies, $parameters);
        $resolved = DefinitionResolver::resolveArray($container, $dependencies);
        /** @psalm-suppress MixedMethodCall */
        $object = new $class(...array_values($resolved));
        $config = DefinitionResolver::resolveArray($container, $definition->getConfig());

        return $this->configure($object, $config);
    }

    /**
     * @psalm-param array<string, DefinitionInterface> $dependencies
     *
     * @throws InvalidConfigException
     */
    private function injectParameters(array &$dependencies, array $parameters): void
    {
        $isIntegerIndexed = $this->isIntegerIndexed($parameters);
        $dependencyIndex = 0;
        $usedParameters = [];
        $isVariadic = false;
        foreach ($dependencies as $key => &$value) {
            if ($value instanceof ParameterDefinition && $value->getParameter()->isVariadic()) {
                $isVariadic = true;
            }
            $index = $isIntegerIndexed ? $dependencyIndex : $key;
            if (array_key_exists($index, $parameters)) {
                $value = DefinitionResolver::ensureResolvable($parameters[$index]);
                $usedParameters[$index] = 1;
            }
            $dependencyIndex++;
        }
        unset($value);
        if ($isVariadic) {
            /** @var mixed $value */
            foreach ($parameters as $index => $value) {
                if (!isset($usedParameters[$index])) {
                    $dependencies[$index] = DefinitionResolver::ensureResolvable($value);
                }
            }
        }
        /** @psalm-var array<string, DefinitionInterface> $dependencies */
    }

    private function isIntegerIndexed(array $parameters): bool
    {
        $hasStringIndex = false;
        $hasIntegerIndex = false;

        /** @var mixed $parameter */
        foreach ($parameters as $index => $parameter) {
            if (is_string($index)) {
                $hasStringIndex = true;
                if ($hasIntegerIndex) {
                    break;
                }
            } else {
                $hasIntegerIndex = true;
                if ($hasStringIndex) {
                    break;
                }
            }
        }
        if ($hasIntegerIndex && $hasStringIndex) {
            throw new InvalidConfigException(
                'Parameters indexed both by name and by position are not allowed in the same array.'
            );
        }

        return $hasIntegerIndex;
    }

    /**
     * Returns the dependencies of the specified class.
     *
     * @param class-string $class class name, interface name or alias name
     *
     * @throws NotInstantiableException
     *
     * @return DefinitionInterface[] The dependencies of the specified class.
     * @psalm-return array<string, DefinitionInterface>
     */
    private function getDependencies(string $class): array
    {
        if (!isset(self::$dependencies[$class])) {
            self::$dependencies[$class] = $this->getExtractor()->fromClassName($class);
        }

        return self::$dependencies[$class];
    }

    private function getExtractor(): DefinitionExtractor
    {
        if (static::$extractor === null) {
            // For now use hard coded extractor.
            static::$extractor = new DefinitionExtractor();
        }

        return static::$extractor;
    }

    /**
     * Configures an object with the given configuration.
     *
     * @param object $object The object to be configured.
     * @param array $config Property values and methods to call.
     *
     * @psalm-param array<string,mixed> $config
     *
     * @return object The object itself.
     */
    private function configure(object $object, array $config): object
    {
        /** @var mixed $arguments */
        foreach ($config as $action => $arguments) {
            if (substr($action, -2) === '()') {
                // method call
                /** @var mixed */
                $setter = call_user_func_array([$object, substr($action, 0, -2)], $arguments);
                if ($setter instanceof $object) {
                    /** @var object */
                    $object = $setter;
                }
            } else {
                // property
                $object->$action = $arguments;
            }
        }

        return $object;
    }
}
