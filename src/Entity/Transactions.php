<?php

namespace App\Entity;


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\TransactionRepository;

#[ORM\Entity(repositoryClass: TransactionRepository::class)]
class Transactions
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 10)]
    #[Assert\Choice(choices: ['deposit', 'withdrawal'], message: 'Invalid transaction type')]
    private ?string $type = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?float $amount = null;

    #[ORM\Column(type: 'string', length: 3)]
    private ?string $currency = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $date = null;


    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'wins')]
    #[ORM\JoinColumn(nullable: false, name: 'user_id', referencedColumnName: 'id_utilisateur', onDelete: 'CASCADE')]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
