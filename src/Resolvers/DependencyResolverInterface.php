<?php


namespace Yiisoft\Factory\Resolvers;

use Yiisoft\Factory\Exceptions\NotInstantiableException;

/**
 * Interface DependencyResolverInterface
 */
interface DependencyResolverInterface
{
    /**
     * @param string $class
     * @return Definition[] An array of direct dependencies of $class.
     * @throws NotInstantiableException If the class is not instantiable this MUST throw a NotInstantiableException
     */
    public function resolveConstructor(string $class): array;

    /**
     * @param callable $callable
     * @return Definition[] An array of direct dependencies of the callable.
     */
    public function resolveCallable(callable $callable): array;
}
