<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'Un compte est déjà lié à cette adresse email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_utilisateur', type: 'bigint')]
    private ?int $id = null;

    #[ORM\Column(name: 'balance', type: 'decimal', precision: 10, scale: 2, nullable: false, options: ['default' => 0])]
    private ?int $balance = 0;

    #[ORM\Column(name: 'username', type: 'string', length: 255, nullable: false)]
    private ?string $username = null;

    #[ORM\Column(name: 'password', type: 'string', length: 255, nullable: false)]
    private ?string $password = null;

    #[ORM\Column(name: 'email', type: 'string', length: 255)]
    private ?string $email = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isVerified = false;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Win::class, orphanRemoval: true)]
    private Collection $wins;

    public function __construct()
    {
        $this->wins = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBalance(): ?int
    {
        return $this->balance;
    }

    public function setBalance(int $balance): self
    {
        $this->balance = $balance;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function eraseCredentials(): void
    {
        // Implement if needed
    }

    public function getUserIdentifier(): string
    {
        return $this->getEmail();
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getWins(): Collection
    {
        return $this->wins;
    }

    public function addWin(Win $win): self
    {
        if (!$this->wins->contains($win)) {
            $this->wins->add($win);
            $win->setUser($this);
        }

        return $this;
    }

    public function removeWin(Win $win): self
    {
        if ($this->wins->removeElement($win)) {
            // set the owning side to null (unless already changed)
            if ($win->getUser() === $this) {
                $win->setUser(null);
            }
        }

        return $this;
    }
}