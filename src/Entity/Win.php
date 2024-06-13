<?php

namespace App\Entity;

use App\Repository\WinRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WinRepository::class)]
class Win
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?string $bet = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?string $amount = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $machineName = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $date = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'wins')]
    #[ORM\JoinColumn(nullable: false, name: 'user_id', referencedColumnName: 'id_utilisateur', onDelete: 'CASCADE')]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBet(): ?string
    {
        return $this->bet;
    }

    public function setBet(string $bet): self
    {
        $this->bet = $bet;

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

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getMachineName(): ?string
    {
        return $this->machineName;
    }

    public function setMachineName(string $machineName): self
    {
        $this->machineName = $machineName;

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
