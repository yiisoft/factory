<?php

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
class Reference implements DefinitionInterface
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

    public static function to(string $id): Reference
    {
        return new self($id);
    }

    public function resolve(ContainerInterface $container, array $params = [])
    {
        if (empty($params)) {
            $result = $container->get($this->id);
        } else {
            /** @noinspection PhpMethodParametersCountMismatchInspection passing parameters for containers supporting them */
            $container->set($this->id, [
                'class' => $this->id,
                '__construct()' => $params
            ]);
            $result = $container->get($this->id);
        }

        return $result;
    }
}
