<?php

namespace App\Entity;

use App\Repository\UserRepository;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity(
    fields: ['email'],
    message: 'It looks like your already have an account with this email!',
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'The field cannot be blank')]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'The field cannot be blank')]
    private ?string $lastName = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $birthdate = null;

    #[ORM\Column(length: 255)]
    private ?string $phoneNumber = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $street = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $zipCode = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $lastLogin = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $lastFailedLogin = null;

    #[ORM\Column(nullable: true)]
    private ?int $loginCount = null;

    #[ORM\Column(nullable: true)]
    private ?int $failedLoginCount = null;

    #[ORM\Column]
    private ?bool $isActive = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tokenUpdatePassword = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $passwordRequestedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'createdBy', targetEntity: Company::class)]
    private Collection $companiesCreatedBy;

    #[ORM\OneToMany(mappedBy: 'updatedBy', targetEntity: Company::class)]
    private Collection $companiesUpdatedBy;

    #[ORM\OneToMany(mappedBy: 'createdBy', targetEntity: Application::class)]
    private Collection $applicationsCreatedBy;

    #[ORM\OneToMany(mappedBy: 'updatedBy', targetEntity: Application::class)]
    private Collection $applicationsUpdatedBy;

    public function __construct()
    {
        $this->companiesCreatedBy = new ArrayCollection();
        $this->companiesUpdatedBy = new ArrayCollection();
        $this->applicationsCreatedBy = new ArrayCollection();
        $this->applicationsUpdatedBy = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getBirthdate(): ?\DateTimeInterface
    {
        return $this->birthdate;
    }

    public function setBirthdate(?\DateTimeInterface $birthdate): static
    {
        $this->birthdate = $birthdate;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): static
    {
        $this->photo = $photo;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): static
    {
        $this->street = $street;

        return $this;
    }

    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    public function setZipCode(?string $zipCode): static
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    public function getLastLogin(): ?\DateTimeInterface
    {
        return $this->lastLogin;
    }

    public function setLastLogin(?\DateTimeInterface $lastLogin): static
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }

    public function getLastFailedLogin(): ?\DateTimeInterface
    {
        return $this->lastFailedLogin;
    }

    public function setLastFailedLogin(?\DateTimeInterface $lastFailedLogin): static
    {
        $this->lastFailedLogin = $lastFailedLogin;

        return $this;
    }

    public function getLoginCount(): ?int
    {
        return $this->loginCount;
    }

    public function setLoginCount(?int $loginCount): static
    {
        $this->loginCount = $loginCount;

        return $this;
    }

    public function getFailedLoginCount(): ?int
    {
        return $this->failedLoginCount;
    }

    public function setFailedLoginCount(?int $failedLoginCount): static
    {
        $this->failedLoginCount = $failedLoginCount;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getTokenUpdatePassword(): ?string
    {
        return $this->tokenUpdatePassword;
    }

    public function setTokenUpdatePassword(?string $tokenUpdatePassword): static
    {
        $this->tokenUpdatePassword = $tokenUpdatePassword;

        return $this;
    }

    public function getPasswordRequestedAt(): ?\DateTimeInterface
    {
        return $this->passwordRequestedAt;
    }

    public function setPasswordRequestedAt(): static
    {
        $this->passwordRequestedAt = new DateTimeImmutable('now', new DateTimeZone('Africa/Tunis'));

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(): self
    {
        $this->createdAt = new DateTimeImmutable('now', new DateTimeZone('Africa/Tunis'));

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(): static
    {
        $this->updatedAt = new DateTimeImmutable('now', new DateTimeZone('Africa/Tunis'));

        return $this;
    }

    /**
     * @return Collection<int, Company>
     */
    public function getCompaniesCreatedBy(): Collection
    {
        return $this->companiesCreatedBy;
    }

    public function addCompaniesCreatedBy(Company $companiesCreatedBy): static
    {
        if (!$this->companiesCreatedBy->contains($companiesCreatedBy)) {
            $this->companiesCreatedBy->add($companiesCreatedBy);
            $companiesCreatedBy->setCreatedBy($this);
        }

        return $this;
    }

    public function removeCompaniesCreatedBy(Company $companiesCreatedBy): static
    {
        if ($this->companiesCreatedBy->removeElement($companiesCreatedBy)) {
            // set the owning side to null (unless already changed)
            if ($companiesCreatedBy->getCreatedBy() === $this) {
                $companiesCreatedBy->setCreatedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Company>
     */
    public function getCompaniesUpdatedBy(): Collection
    {
        return $this->companiesUpdatedBy;
    }

    public function addCompaniesUpdatedBy(Company $companiesUpdatedBy): static
    {
        if (!$this->companiesUpdatedBy->contains($companiesUpdatedBy)) {
            $this->companiesUpdatedBy->add($companiesUpdatedBy);
            $companiesUpdatedBy->setUpdatedBy($this);
        }

        return $this;
    }

    public function removeCompaniesUpdatedBy(Company $companiesUpdatedBy): static
    {
        if ($this->companiesUpdatedBy->removeElement($companiesUpdatedBy)) {
            // set the owning side to null (unless already changed)
            if ($companiesUpdatedBy->getUpdatedBy() === $this) {
                $companiesUpdatedBy->setUpdatedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Application>
     */
    public function getApplicationsCreatedBy(): Collection
    {
        return $this->applicationsCreatedBy;
    }

    public function addApplicationsCreatedBy(Application $applicationsCreatedBy): static
    {
        if (!$this->applicationsCreatedBy->contains($applicationsCreatedBy)) {
            $this->applicationsCreatedBy->add($applicationsCreatedBy);
            $applicationsCreatedBy->setCreatedBy($this);
        }

        return $this;
    }

    public function removeApplicationsCreatedBy(Application $applicationsCreatedBy): static
    {
        if ($this->applicationsCreatedBy->removeElement($applicationsCreatedBy)) {
            // set the owning side to null (unless already changed)
            if ($applicationsCreatedBy->getCreatedBy() === $this) {
                $applicationsCreatedBy->setCreatedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Application>
     */
    public function getApplicationsUpdatedBy(): Collection
    {
        return $this->applicationsUpdatedBy;
    }

    public function addApplicationsUpdatedBy(Application $applicationsUpdatedBy): static
    {
        if (!$this->applicationsUpdatedBy->contains($applicationsUpdatedBy)) {
            $this->applicationsUpdatedBy->add($applicationsUpdatedBy);
            $applicationsUpdatedBy->setUpdatedBy($this);
        }

        return $this;
    }

    public function removeApplicationsUpdatedBy(Application $applicationsUpdatedBy): static
    {
        if ($this->applicationsUpdatedBy->removeElement($applicationsUpdatedBy)) {
            // set the owning side to null (unless already changed)
            if ($applicationsUpdatedBy->getUpdatedBy() === $this) {
                $applicationsUpdatedBy->setUpdatedBy(null);
            }
        }

        return $this;
    }


}
