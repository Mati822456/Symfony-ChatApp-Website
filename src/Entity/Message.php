<?php

namespace App\Entity;

use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\MessageRepository;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['api_public'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['api_public'])]
    private ?User $incoming_msg = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['api_public'])]
    private ?User $outgoing_msg = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['api_public'])]
    private ?string $msg = null;

    #[ORM\Column(length: 255)]
    #[Groups(['api_public'])]
    private ?DateTime $created = null;

    #[ORM\Column(type: Types::SMALLINT)]
    #[Groups(['api_public'])]
    private ?int $status = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIncomingMsg(): ?User
    {
        return $this->incoming_msg;
    }

    public function setIncomingMsg(?User $incoming_msg): self
    {
        $this->incoming_msg = $incoming_msg;

        return $this;
    }

    public function getOutgoingMsg(): ?User
    {
        return $this->outgoing_msg;
    }

    public function setOutgoingMsg(?User $outgoing_msg): self
    {
        $this->outgoing_msg = $outgoing_msg;

        return $this;
    }

    public function getMsg(): ?string
    {
        return $this->msg;
    }

    public function setMsg(string $msg): self
    {
        $this->msg = $msg;

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

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }
}
