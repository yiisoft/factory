<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Definition;

use Yiisoft\Factory\Exception\InvalidConfigException;
use Yiisoft\Factory\DependencyResolverInterface;

use function is_callable;
use function is_object;

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
final class DynamicReference implements ReferenceInterface
{
    private DefinitionInterface $definition;

    /**
     * @param mixed $definition
     *
     * @throws InvalidConfigException
     */
    private function __construct($definition)
    {
        if (is_object($definition) && !is_callable($definition)) {
            throw new InvalidConfigException('DynamicReference don\'t support object as definition.');
        }

        $this->definition = Normalizer::normalize($definition);
    }

    /**
     * @see Normalizer
     *
     * @throws InvalidConfigException
     */
    public static function to($id): ReferenceInterface
    {
        return new self($id);
    }

    public function resolve(DependencyResolverInterface $container)
    {
        return $this->definition->resolve($container);
    }
}
