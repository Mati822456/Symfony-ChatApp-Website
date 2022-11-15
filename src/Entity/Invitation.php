<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\InvitationRepository;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: InvitationRepository::class)]
class Invitation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\User', fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'frm_user_id', referencedColumnName: 'id', nullable: false)]
    #[Groups(['api_public'])]
    private ?User $frm_user = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\User', fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'to_user_id', referencedColumnName: 'id', nullable: false)]
    #[Groups(['api_public'])]
    private ?User $to_user = null;

    #[ORM\Column(length: 255)]
    #[Groups(['api_public'])]
    private ?DateTime $created = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFrmUser(): ?User
    {
        return $this->frm_user;
    }

    public function setFrmUser(?User $frm_user): self
    {
        $this->frm_user = $frm_user;

        return $this;
    }

    public function getToUser(): ?User
    {
        return $this->to_user;
    }

    public function setToUser(?User $to_user): self
    {
        $this->to_user = $to_user;

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
