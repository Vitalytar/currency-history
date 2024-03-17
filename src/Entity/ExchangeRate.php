<?php

namespace App\Entity;

use App\Repository\ExchangeRateRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExchangeRateRepository::class)]
class ExchangeRate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 3)]
    private ?string $base_currency = null;

    #[ORM\Column(length: 3)]
    private ?string $target_currency = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 6)]
    private ?string $rate = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $fetched_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBaseCurrency(): ?string
    {
        return $this->base_currency;
    }

    public function setBaseCurrency(string $base_currency): static
    {
        $this->base_currency = $base_currency;

        return $this;
    }

    public function getTargetCurrency(): ?string
    {
        return $this->target_currency;
    }

    public function setTargetCurrency(string $target_currency): static
    {
        $this->target_currency = $target_currency;

        return $this;
    }

    public function getRate(): ?string
    {
        return $this->rate;
    }

    public function setRate(string $rate): static
    {
        $this->rate = $rate;

        return $this;
    }

    public function getFetchedAt(): ?\DateTimeImmutable
    {
        return $this->fetched_at;
    }

    public function setFetchedAt(\DateTimeImmutable $fetched_at): static
    {
        $this->fetched_at = $fetched_at;

        return $this;
    }
}
