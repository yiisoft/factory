<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Definition;

use Yiisoft\Factory\Exception\InvalidConfigException;
use Yiisoft\Factory\DependencyResolverInterface;

use function is_string;

/**
 * The `Reference` defines a dependency to a service in the container or factory in another service definition.
 * For example:
 *
 * ```php
 * [
 *    InterfaceA::class => ConcreteA::class,
 *    'alternativeForA' => ConcreteB::class,
 *    MyService::class => [
 *        '__construct()' => [
 *            Reference::to('alternativeForA'),
 *        ],
 *    ],
 * ]
 * ```
 */
final class Reference implements ReferenceInterface
{
    private string $id;

    private function __construct(string $id)
    {
        $this->id = $id;
    }

    /**
     * @throws InvalidConfigException If ID is not string.
     */
    public static function to($id): self
    {
        if (!is_string($id)) {
            throw new InvalidConfigException('Reference ID must be string.');
        }

        return new self($id);
    }

    public function resolve(DependencyResolverInterface $container)
    {
        return $container->resolveReference($this->id);
    }
}
