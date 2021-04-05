<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Definitions\Arrays;

use Psr\Container\ContainerInterface;
use Yiisoft\Factory\Definitions\DefinitionInterface;
use Yiisoft\Factory\Definitions\DefinitionResolver;
use Yiisoft\Factory\Definitions\ParameterDefinition;
use Yiisoft\Factory\Exceptions\InvalidConfigException;
use Yiisoft\Factory\Exceptions\NotInstantiableException;
use Yiisoft\Factory\Extractors\DefinitionExtractor;

use function array_key_exists;
use function call_user_func_array;
use function is_string;

/**
 * Builds object by ArrayDefinition.
 */
final class Builder
{
    private static ?self $instance = null;
    private static ?DefinitionExtractor $extractor = null;

    /**
     * @psalm-var array<string, array<string, DefinitionInterface>>
     */
    private static array $dependencies = [];

    private function __construct()
    {
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @throws NotInstantiableException
     * @throws InvalidConfigException
     */
    public function build(ContainerInterface $container, ArrayDefinition $definition): object
    {
        $class = $definition->getClass();
        $dependencies = $this->getDependencies($class);
        $constructorParameters = $definition->getConstructorParameters();
        $this->injectParameters($dependencies, $constructorParameters);

        $resolved = DefinitionResolver::resolveArray($container, $dependencies);

        /** @psalm-suppress MixedMethodCall */
        $object = new $class(...array_values($resolved));

        /** @psalm-var array<string,array> $calls */
        $calls = DefinitionResolver::resolveArray($container, $definition->getCallMethods());
        foreach ($calls as $method => $arguments) {
            /** @var mixed */
            $setter = call_user_func_array([$object, $method], $arguments);
            if ($setter instanceof $object) {
                /** @var object */
                $object = $setter;
            }
        }

        $properties = DefinitionResolver::resolveArray($container, $definition->getSetProperties());
        /** @var mixed $value */
        foreach ($properties as $property => $value) {
            $object->$property = $value;
        }

        return $object;
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

        foreach ($parameters as $index => $_parameter) {
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
     * @param class-string $class Class name or interface name.
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
}
