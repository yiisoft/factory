<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Definitions;

use Psr\Container\ContainerInterface;
use Yiisoft\Factory\Exceptions\NotInstantiableException;
use Yiisoft\Factory\Extractors\DefinitionExtractor;

/**
 * Builds object by ArrayDefinition.
 */
class ArrayBuilder
{
    private static ?DefinitionExtractor $extractor = null;
    private static array $dependencies = [];

    public function build(ContainerInterface $container, ArrayDefinition $definition)
    {
        $class = $definition->getClass();
        $dependencies = $this->getDependencies($class);
        $parameters = $definition->getParams();

        if (!empty($parameters)) {
            $this->validateParameters($parameters);

            foreach ($parameters as $index => $parameter) {
                $this->injectParameter($dependencies, $index, DefinitionResolver::ensureResolvable($parameter));
            }
        }

        $resolved = DefinitionResolver::resolveArray($container, $dependencies);
        $object = new $class(...array_values($resolved));
        return $this->configure($container, $object, $definition->getConfig());
    }

    private function validateParameters(array $parameters): void
    {
        $hasStringParameter = false;
        $hasIntParameter = false;
        foreach ($parameters as $index => $parameter) {
            if (is_string($index)) {
                $hasStringParameter = true;
                if ($hasIntParameter) {
                    break;
                }
            } else {
                $hasIntParameter = true;
                if ($hasStringParameter) {
                    break;
                }
            }
        }
        if ($hasIntParameter && $hasStringParameter) {
            throw new \InvalidArgumentException(
                'Parameters indexed by name and by position in the same array are not allowed.'
            );
        }
    }

    private function injectParameter(array &$dependencies, $index, $parameter): void
    {
        if (is_string($index)) {
            $dependencies[$index] = $parameter;
        } else {
            reset($dependencies);
            $dependencyIndex = 0;
            while (current($dependencies)) {
                if ($index === $dependencyIndex) {
                    $dependencies[key($dependencies)] = $parameter;
                    break;
                }
                next($dependencies);
                $dependencyIndex++;
            }
        }
    }

    /**
     * Returns the dependencies of the specified class.
     * @param string $class class name, interface name or alias name
     * @return DefinitionInterface[] the dependencies of the specified class.
     * @throws NotInstantiableException
     * @internal
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
     * @param ContainerInterface $container
     * @param object $object the object to be configured
     * @param iterable $config property values and methods to call
     * @return object the object itself
     */
    private function configure(ContainerInterface $container, $object, iterable $config)
    {
        foreach ($config as $action => $arguments) {
            if (substr($action, -2) === '()') {
                // method call
                $setter = \call_user_func_array([$object, substr($action, 0, -2)], $arguments);
                if ($setter instanceof $object) {
                    $object = $setter;
                }
            } else {
                // property
                if ($arguments instanceof DefinitionInterface) {
                    $arguments = $arguments->resolve($container);
                }
                $object->$action = $arguments;
            }
        }

        return $object;
    }
}
