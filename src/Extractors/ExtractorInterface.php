<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Extractors;

use Yiisoft\Factory\Definitions\DefinitionInterface;
use Yiisoft\Factory\Exceptions\NotInstantiableException;

/**
 * Interface ExtractorInterface
 */
interface ExtractorInterface
{
    /**
     * @param string $class
     * @return DefinitionInterface[] An array of direct dependencies of $class.
     * @throws NotInstantiableException If the class is not instantiable this MUST throw a NotInstantiableException
     */
    public function fromClassName(string $class): array;

    /**
     * @param callable $callable
     * @return DefinitionInterface[] An array of direct dependencies of the callable.
     */
    public function fromCallable(callable $callable): array;
}
