<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\CustomIdGenerator(UuidGenerator::class)]
    private Uuid $id;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(length: 255, nullable: true)]
    private null|string $password = null;

    #[ORM\Column(length: 255, nullable: true)]
    private null|string $email = null;

    #[ORM\Column(length: 255, unique: true, nullable: true)]
    private ?string $fingerprint = null;

    /**
     * @var Collection<int, Guess>
     */
    #[ORM\OneToMany(targetEntity: Guess::class, mappedBy: 'owner')]
    private Collection $guesses;

    #[ORM\Column]
    private DateTimeImmutable $createdAt;

    /**
     * @var string[]
     */
    #[ORM\Column(type: Types::JSON)]
    private array $roles = [];

    public function __construct(
        string $name,
        string $fingerprint
    )
    {
        $this->name = $name;
        $this->fingerprint = $fingerprint;
        $this->guesses = new ArrayCollection();
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @param string[] $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function eraseCredentials(): void
    {

    }

    public function getUserIdentifier(): string
    {
        return $this->getFingerprint();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }

    public function getFingerprint(): ?string
    {
        return $this->fingerprint;
    }

    public function setFingerprint(?string $fingerprint): void
    {
        $this->fingerprint = $fingerprint;
    }

    /**
     * @return Collection<int, Guess>
     */
    public function getGuesses(): Collection
    {
        return $this->guesses;
    }

    public function addGuess(Guess $guess): static
    {
        if (! $this->guesses->contains($guess)) {
            $this->guesses->add($guess);
        }

        return $this;
    }

    public function removeGuess(Guess $guess): static
    {
        $this->guesses->removeElement($guess);

        return $this;
    }
}
