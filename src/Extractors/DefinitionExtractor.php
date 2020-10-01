<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Extractors;

use Yiisoft\Factory\Definitions\DefinitionInterface;
use Yiisoft\Factory\Definitions\ClassDefinition;
use Yiisoft\Factory\Definitions\ParameterDefinition;
use Yiisoft\Factory\Exceptions\NotInstantiableException;

/**
 * Class DefinitionExtractor
 * This implementation resolves dependencies by using class type hints.
 * Note that service names need not match the parameter names, parameter names are ignored
 */
class DefinitionExtractor implements ExtractorInterface
{
    public function fromClassName(string $class): array
    {
        $reflectionClass = new \ReflectionClass($class);
        if (!$reflectionClass->isInstantiable()) {
            throw new NotInstantiableException($class);
        }
        $constructor = $reflectionClass->getConstructor();
        return $constructor === null ? [] : $this->fromFunction($constructor);
    }

    /**
     * @suppress PhanUndeclaredMethod
     */
    private function fromFunction(\ReflectionFunctionAbstract $reflectionFunction): array
    {
        $result = [];
        foreach ($reflectionFunction->getParameters() as $parameter) {
            $result[$parameter->getName()] = $this->fromParameter($parameter);
        }
        return $result;
    }

    /**
     * @suppress PhanUndeclaredMethod
     */
    private function fromParameter(\ReflectionParameter $parameter): DefinitionInterface
    {
        $type = $parameter->getType();
        $hasDefault = $parameter->isOptional() || $parameter->isDefaultValueAvailable() || (isset($type) && $type->allowsNull());

        // Our parameter has a class type hint
        if ($type !== null && !$type->isBuiltin()) {
            return new ClassDefinition($type->getName(), $type->allowsNull());
        }

        // Our parameter does not have a class type hint and either has a default value or is nullable.
        return new ParameterDefinition(
            $parameter,
            $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null,
            $type !== null ? $type->getName() : null
        );
    }

    public function fromCallable(callable $callable): array
    {
        return $this->fromFunction(new \ReflectionFunction(\Closure::fromCallable($callable)));
    }
}
