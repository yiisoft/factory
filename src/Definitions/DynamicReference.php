<?php

namespace Yiisoft\Factory\Definitions;

use Psr\Container\ContainerInterface;

/**
 * Class DynamicReference allows us to define a dependency to a service not defined in the container.
 * This class implements the array configuration syntax common to Yii
 * For example:
 * ```php
 * [
 *    InterfaceA::class => ConcreteA::class,
 *    'alternativeForA' => ConcreteB::class,
 *    Service1::class => [
 *        '__construct()' => [
 *            DynamicReference::to([
 *                '__class' => SomeClass::class,
 *                'someProp' => 15
 *            ]
 *        ]
 *    ]
 * ]
 * ```
 */
class DynamicReference implements DefinitionInterface
{
    private $definition;

    private function __construct($definition)
    {
        $this->definition = Normalizer::normalize($definition);
    }

    public static function to($definition): DynamicReference
    {
        return new self($definition);
    }

    public function resolve(ContainerInterface $container)
    {
        return $this->definition->resolve($container);
    }
}
