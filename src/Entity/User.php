<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Put;
use App\Controller\DeleteUsersController;
use App\Dto\UsersStatisticsDto;
use App\Repository\UserRepository;
use App\State\UsersStatisticsProvider;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    normalizationContext: ['groups' => ['user:read']],
    denormalizationContext: ['groups' => ['user:write']],
)]
#[Delete(
    description: 'Suppression d\'un compte utilisateur pour un id données',
    security: 'is_granted("ROLE_USER") and is_granted("CAN_DELETE", object)',
    name: 'api_users_delete_item',
)]
#[Patch(
    uriTemplate: '/admin/users/{id}',
    description: 'Modifier les informations d\'un utilisateur pour un id donné',
)]
#[Get(
    uriTemplate: '/admin/users/{id}',
    description: 'Obtenir les informations d\'un utilisateur pour un id données',
)]
#[GetCollection(
    uriTemplate: '/admin/users',
    description: 'Obtenir la liste de tous les utilisateurs de l\'application',
)]
#[Put(
    uriTemplate: '/admin/users/{id}',
    description: 'Remplacer l\'intégralitée des informations d\'un utilisateur pour un id donné',
)]
#[Delete(
    uriTemplate: '/admin/users',
    controller: DeleteUsersController::class,
    description: 'Suppression de l\'intégralitée des utilisateurs de l\'application',
)]
#[Get(
    uriTemplate: '/admin/users-stats',
    description: 'Obtenir les statistiques des utilisateurs de l\'application',
    output: UsersStatisticsDto::class,
    provider: UsersStatisticsProvider::class,
)]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'Un compte est déjà lié à cette adresse email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[Groups(['user:read'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_utilisateur', type: 'bigint')]
    private ?int $id = null;

    #[Groups(['user:read'])]
    #[ORM\Column(name: 'balance', type: 'decimal', precision: 10, scale: 2, nullable: false, options: ['default' => 0])]
    private ?int $balance = 0;

    #[Groups(['user:read'])]
    #[ORM\Column(name: 'username', type: 'string', length: 255, nullable: false)]
    private ?string $username = null;

    #[ORM\Column(name: 'password', type: 'string', length: 255, nullable: false)]
    private ?string $password = null;

    #[Groups(['user:read'])]
    #[ORM\Column(name: 'email', type: 'string', length: 255)]
    private ?string $email = null;

    #[Groups(['user:read'])]
    #[ORM\Column(type: 'boolean')]
    private bool $isVerified = false;

    #[ORM\OneToMany(targetEntity: Win::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $wins;

    public function __construct()
    {
        $this->wins = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBalance(): ?float
    {
        return $this->balance;
    }

    public function setBalance(float $balance): self
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

    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(Transaction $transaction): self
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions->add($transaction);
            $transaction->setUser($this);
        }

        return $this;
    }

    public function removeTransaction(Transaction $transaction): self
    {
        if ($this->transactions->removeElement($transaction)) {
            if ($transaction->getUser() === $this) {
                $transaction->setUser(null);
            }
        }

        return $this;
    }
}
