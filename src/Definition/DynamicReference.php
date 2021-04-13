<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Definition;

use Psr\Container\ContainerInterface;

/**
 * Class DynamicReference allows us to define a dependency to a service not defined in the container.
 * Definition may be defined multiple ways {@see Normalizer}.
 * For example:
 * ```php
 * [
 *    InterfaceA::class => ConcreteA::class,
 *    'alternativeForA' => ConcreteB::class,
 *    Service1::class => [
 *        '__construct()' => [
 *            DynamicReference::to([
 *                'class' => SomeClass::class,
 *                '$someProp' => 15
 *            ])
 *        ]
 *    ]
 * ]
 * ```
 */
class DynamicReference implements ReferenceInterface
{
    private DefinitionInterface $definition;

    /**
     * @param mixed $definition
     */
    private function __construct($definition)
    {
        $this->definition = Normalizer::normalize($definition);
    }

    /**
     * @param mixed $definition
     *
     * @return ReferenceInterface
     *
     * {@see Normalizer}
     */
    public static function to($definition): ReferenceInterface
    {
        return new self($definition);
    }

    public function resolve(ContainerInterface $container)
    {
        return $this->definition->resolve($container);
    }
}
