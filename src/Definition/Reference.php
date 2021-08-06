<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Definition;

use Yiisoft\Factory\Exception\InvalidConfigException;
use Yiisoft\Factory\DependencyResolverInterface;

use function is_string;

/**
 * Class Reference allows us to define a dependency to a service in the container in another service definition.
 * For example:
 * ```php
 * [
 *    InterfaceA::class => ConcreteA::class,
 *    'alternativeForA' => ConcreteB::class,
 *    Service1::class => [
 *        '__construct()' => [
 *            Reference::to('alternativeForA')
 *        ]
 *    ]
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

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     *
     * @throws InvalidConfigException
     */
    public static function to($id): ReferenceInterface
    {
        if (!is_string($id)) {
            throw new InvalidConfigException('Reference id must be string.');
        }

        return new self($id);
    }

    public function resolve(DependencyResolverInterface $container)
    {
        return $container->resolveReference($this->id);
    }
}
