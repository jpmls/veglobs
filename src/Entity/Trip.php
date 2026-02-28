<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
class Trip
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['trip:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'trips')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 120)]
    #[Groups(['trip:read'])]
    private string $fromStation;

    #[ORM\Column(length: 120)]
    #[Groups(['trip:read'])]
    private string $toStation;

    #[ORM\Column(length: 30)]
    #[Groups(['trip:read'])]
    private string $network;

    #[ORM\Column(length: 30)]
    #[Groups(['trip:read'])]
    private string $line;

    #[ORM\Column(type: 'json', nullable: true)]
    #[Groups(['trip:read'])]
    private ?array $payload = null;

    #[ORM\Column]
    #[Groups(['trip:read'])]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): self { $this->user = $user; return $this; }

    public function getFromStation(): string { return $this->fromStation; }
    public function setFromStation(string $v): self { $this->fromStation = $v; return $this; }

    public function getToStation(): string { return $this->toStation; }
    public function setToStation(string $v): self { $this->toStation = $v; return $this; }

    public function getNetwork(): string { return $this->network; }
    public function setNetwork(string $v): self { $this->network = $v; return $this; }

    public function getLine(): string { return $this->line; }
    public function setLine(string $v): self { $this->line = $v; return $this; }

    public function getPayload(): ?array { return $this->payload; }
    public function setPayload(?array $v): self { $this->payload = $v; return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}