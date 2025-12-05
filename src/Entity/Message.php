<?php

namespace App\Entity;

use App\Enum\MessageRole;
use App\Enum\MessageStatus;
use App\Repository\MessageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $message = null;

    #[ORM\Column(enumType: MessageStatus::class)]
    private MessageStatus|null $status = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(targetEntity: ClientSession::class, inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?ClientSession $clientSession = null;

    #[ORM\Column(enumType: MessageRole::class)]
    private MessageRole $role;

    #[ORM\ManyToOne(inversedBy: 'messages')]
    private ?User $operator = null;

    public function __toString(): string
    {
        return 'ID - '.$this->id.' '.$this->message.' '.$this->createdAt->format('Y-m-d H:i:s');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function getStatus(): ?MessageStatus
    {
        return $this->status;
    }

    public function setStatus(MessageStatus $status): void
    {
        $this->status = $status;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getClientSession(): ?ClientSession
    {
        return $this->clientSession;
    }

    public function setClientSession(?ClientSession $clientSession): static
    {
        $this->clientSession = $clientSession;

        return $this;
    }

    public function getRole(): MessageRole
    {
        return $this->role;
    }

    public function setRole(MessageRole $role): void
    {
        $this->role = $role;
    }

    public function getOperator(): ?User
    {
        return $this->operator;
    }

    public function setOperator(?User $operator): static
    {
        $this->operator = $operator;

        return $this;
    }
}
