<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Definitions;

use Psr\Container\ContainerInterface;

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
class Reference implements ReferenceInterface
{
    private $id;

    private function __construct($id)
    {
        $this->id = $id;
    }

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return ReferenceInterface
     */
    public static function to($id): ReferenceInterface
    {
        if (!\is_string($id)) {
            throw new \RuntimeException('$id should be string.');
        }
        return new self($id);
    }

    public function resolve(ContainerInterface $container)
    {
        return $container->get($this->id);
    }
}
