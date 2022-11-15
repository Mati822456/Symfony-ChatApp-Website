<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\FriendRepository;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: FriendRepository::class)]
class Friend
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\User', fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    #[Groups(['api_public'])]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\User', fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'sec_user_id', referencedColumnName: 'id', nullable: false)]
    #[Groups(['api_public'])]
    private ?User $sec_user = null;

    #[ORM\Column(length: 255)]
    #[Groups(['api_public'])]
    private ?DateTime $created = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getSecUser(): ?User
    {
        return $this->sec_user;
    }

    public function setSecUser(?User $sec_user): self
    {
        $this->sec_user = $sec_user;

        return $this;
    }

    public function getCreated(): ?DateTime
    {
        return $this->created;
    }

    public function setCreated(DateTime $created): self
    {
        $this->created = $created;

        return $this;
    }
}
