<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Support;

final class Immutable
{
    private ?string $id = null;
    private string $fieldImmutable = 'noChange';

    public function id(string $value): void
    {
        $this->id = $value;
    }

    public function fieldImmutable(string $value): self
    {
        $new = clone $this;
        $new->fieldImmutable = $value;
        return $new;
    }
}
