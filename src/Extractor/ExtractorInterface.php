<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Extractor;

use Yiisoft\Factory\Definition\DefinitionInterface;
use Yiisoft\Factory\Exception\NotInstantiableException;

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
