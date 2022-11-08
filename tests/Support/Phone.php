<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Support;

final class Phone
{
    private ?string $id = null;
    private array $colors;
    private array $apps = [];
    private ?string $author = null;
    private ?string $country = null;

    public bool $dev = false;
    public ?string $codeName = null;

    public function __construct(private ?string $name = null, private ?string $version = null, string ...$colors)
    {
        $this->colors = $colors;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function getColors(): array
    {
        return $this->colors;
    }

    public function addApp(string $name, ?string $version = null): void
    {
        $this->apps[] = [$name, $version];
    }

    public function getApps(): array
    {
        return $this->apps;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    public function setId777(): void
    {
        $this->id = '777';
    }

    public function withAuthor(?string $author): self
    {
        $new = clone $this;
        $new->author = $author;
        return $new;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function withCountry(?string $country): self
    {
        $new = clone $this;
        $new->country = $country;
        return $new;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }
}
