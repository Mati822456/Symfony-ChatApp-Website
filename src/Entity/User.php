<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Groups(['api_public'])]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    #[Groups(['api_public'])]
    private ?string $firstname = null;

    #[ORM\Column(length: 255)]
    #[Groups(['api_public'])]
    private ?string $lastname = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Groups(['api_public'])]
    private ?string $uuid = null;

    #[ORM\Column]
    #[Groups(['api_public'])]
    private array $roles = [];

    #[ORM\Column(length: 255)]
    #[Groups(['api_public'])]
    private ?string $img = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['api_public'])]
    private $status = null;

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $last_chat = null;

    #[ORM\Column]
    #[Groups(['api_public'])]
    private ?int $friends = null;

    #[ORM\Column]
    private ?bool $dark_theme = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $apiToken = null;
    
    public function getId(): ?int
    {
        return $this->id;
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

    public function getfirstname(): ?string
    {
        return $this->firstname;
    }

    public function setfirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getlastname(): ?string
    {
        return $this->lastname;
    }

    public function setlastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

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

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getImg(): ?string
    {
        return $this->img;
    }

    public function setImg(string $img): self
    {
        $this->img = $img;

        return $this;
    }

    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }
    
    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getLastChat(): ?string
    {
        return $this->last_chat;
    }

    public function setLastChat(string $last_chat): self
    {
        $this->last_chat = $last_chat;

        return $this;
    }

    public function getFriends(): ?int
    {
        return $this->friends;
    }

    public function setFriends(int $friends): self
    {
        $this->friends = $friends;

        return $this;
    }

    public function isDarkTheme(): ?bool
    {
        return $this->dark_theme;
    }

    public function setDarkTheme(bool $dark_theme): self
    {
        $this->dark_theme = $dark_theme;

        return $this;
    }

    public function getApiToken(): ?string
    {
        return $this->apiToken;
    }

    public function setApiToken(?string $apiToken): self
    {
        $this->apiToken = $apiToken;

        return $this;
    }

}
