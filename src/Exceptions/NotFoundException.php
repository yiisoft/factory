<?php

namespace Yiisoft\Factory\Exceptions;

use Psr\Container\NotFoundExceptionInterface;
use Throwable;

/**
 * NotFoundException is thrown when no entry was found in the container.
 */
class NotFoundException extends \Exception implements NotFoundExceptionInterface
{
    private ?string $id;

    public function __construct(?string $id, string $message = '', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->id = $id;
    }

    public function getId(): ?string
    {
        return $this->id;
    }
}
