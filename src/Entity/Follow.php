<?php

namespace App\Entity;

use App\Repository\FollowRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FollowRepository::class)]
#[ORM\Table(name: 'follow')]
#[ORM\UniqueConstraint(name: 'uniq_follow_user_network_line', columns: ['user_id', 'network', 'line'])]
class Follow
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['follow:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'follows')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 30)]
    #[Groups(['follow:read'])]
    #[Assert\NotBlank(message: 'Network is required')]
    #[Assert\Length(max: 30)]
    private string $network;

    #[ORM\Column(length: 30)]
    #[Groups(['follow:read'])]
    #[Assert\NotBlank(message: 'Line is required')]
    #[Assert\Length(max: 30)]
    private string $line;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['follow:read'])]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getUser(): ?User { return $this->user; }
    public function setUser(User $user): static { $this->user = $user; return $this; }

    public function getNetwork(): string { return $this->network; }
    public function setNetwork(string $network): static { $this->network = $network; return $this; }

    public function getLine(): string { return $this->line; }
    public function setLine(string $line): static { $this->line = $line; return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}