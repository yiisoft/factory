<?php

declare(strict_types=1);

namespace Yiisoft\Factory;

use Psr\Container\ContainerInterface;
use Yiisoft\Definitions\Exception\InvalidConfigException;
use Yiisoft\Definitions\Helpers\DefinitionValidator;

/**
 * Strict factory allows creating objects for specified definitions only.
 *
 * A factory will try to use a PSR-11 compliant container to get dependencies, but will fall back to manual
 * instantiation if the container cannot provide a required dependency.
  */
final class StrictFactory
{
    private FactoryInternalContainer $internalContainer;

    /**
     * @param ContainerInterface|null $container Container to use for resolving dependencies.
     * @param array<string, mixed> $definitions Definitions to create objects with.
     * @param bool $validate If definitions should be validated when set.
     *
     * @throws InvalidConfigException When validation is enabled and definitions are invalid.
     */
    public function __construct(
        ?ContainerInterface $container = null,
        array $definitions = [],
        bool $validate = true,
    ) {
        if ($validate) {
            foreach ($definitions as $id => $definition) {
                DefinitionValidator::validate($definition, $id);
            }
        }

        $this->internalContainer = new FactoryInternalContainer($container, $definitions);
    }

    /**
     * Creates an object using the definition associated with the provided identifier.
     *
     * @param string $id The identifier of the object to create.
     *
     * @throws NotFoundException If no definition is found for the given identifier.
     * @throws InvalidConfigException If definition configuration is not valid.
     * @return mixed The created object.
     */
    public function create(string $id): mixed
    {
        if (!$this->internalContainer->hasDefinition($id)) {
            throw new NotFoundException($id);
        }

        return $this->internalContainer->create(
            $this->internalContainer->getDefinition($id)
        );
    }
}
