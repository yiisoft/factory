<?php

namespace Yiisoft\Factory\Definitions;

use Psr\Container\ContainerInterface;
use Yiisoft\Factory\Exceptions\NotInstantiableException;
use Yiisoft\Factory\Resolvers\ClassNameResolver;

/**
 * Builds object by ArrayDefinition.
 */
class ArrayBuilder
{
    private static $dependencies = [];

    public function build(ContainerInterface $container, ArrayDefinition $definition)
    {
        $class = $definition->getClass();
        $dependencies = $this->getDependencies($class);

        if (!empty($definition->getParams())) {
            foreach (array_values($definition->getParams()) as $index => $param) {
                if ($param instanceof DefinitionInterface) {
                    $dependencies[$index] = $param;
                } else {
                    $dependencies[$index] = new ValueDefinition($param);
                }
            }
        }

        $resolved = $this->resolveDependencies($container, $dependencies);
        $object = new $class(...$resolved);
        return $this->configure($container, $object, $definition->getConfig());
    }

    /**
     * Resolves dependencies by replacing them with the actual object instances.
     * @param ContainerInterface $container
     * @param DefinitionInterface[] $dependencies the dependencies
     * @return array the resolved dependencies
     */
    private function resolveDependencies(ContainerInterface $container, array $dependencies): array
    {
        $container = $container->container ?? $container;
        $result = [];
        /** @var DefinitionInterface $dependency */
        foreach ($dependencies as $dependency) {
            $result[] = $this->resolveDependency($container, $dependency);
        }

        return $result;
    }

    /**
     * This function resolves a dependency recursively, checking for loops.
     * TODO add checking for loops
     * @param ContainerInterface $container
     * @param DefinitionInterface $dependency
     * @return mixed
     */
    private function resolveDependency(ContainerInterface $container, DefinitionInterface $dependency)
    {
        while ($dependency instanceof DefinitionInterface) {
            $dependency = $dependency->resolve($container);
        }
        return $dependency;
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
            self::$dependencies[$class] = $this->getResolver()->resolveConstructor($class);
        }

        return self::$dependencies[$class];
    }

    private static $resolver;

    private function getResolver(): ClassNameResolver
    {
        if (static::$resolver === null) {
            // For now use hard coded resolver.
            static::$resolver = new ClassNameResolver();
        }

        return static::$resolver;
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
                \call_user_func_array([$object, substr($action, 0, -2)], $arguments);
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
