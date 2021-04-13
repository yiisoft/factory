<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Exception;

use Exception;
use Psr\Container\NotFoundExceptionInterface;

/**
 * NotFoundException is thrown when no definition or class was found in the container for a given ID.
 */
class NotFoundException extends Exception implements NotFoundExceptionInterface
{
    private string $id;

    public function __construct(string $id)
    {
        $this->id = $id;
        parent::__construct("No definition or class found for \"$id\".");
    }

    public function getId(): string
    {
        return $this->id;
    }
}
