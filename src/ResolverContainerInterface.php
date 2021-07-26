<?php

declare(strict_types=1);

namespace Yiisoft\Factory;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

interface ResolverContainerInterface extends ContainerInterface
{
    /**
     * @param string $id Identifier of the entry to look for.
     *
     * @throws NotFoundExceptionInterface  No entry was found for this identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     *
     * @return mixed|object
     *
     * @psalm-suppress InvalidThrow
     */
    public function resolve(string $id);

    public function shouldCloneOnResolve(): bool;
}
