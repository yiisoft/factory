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
     * @param class-string $class
     *
     * @throws NotInstantiableException If the class is not instantiable this MUST throw a NotInstantiableException
     *
     * @return DefinitionInterface[] An array of direct dependencies of $class.
     */
    public function fromClassName(string $class): array;

    /**
     * @return DefinitionInterface[] An array of direct dependencies of the callable.
     */
    public function fromCallable(callable $callable): array;
}
