<?php

namespace App\Entity;

use App\Entity\Comment;
use App\Repository\NewsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: NewsRepository::class)]
class News
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['news:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['news:read'])]
    #[Assert\NotBlank(message: 'Title is required')]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Title too short (min 3 chars)',
        maxMessage: 'Title too long (max 255 chars)'
    )]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['news:read'])]
    #[Assert\NotBlank(message: 'Content is required')]
    #[Assert\Length(
        min: 5,
        minMessage: 'Content too short (min 5 chars)'
    )]
    private ?string $content = null;

    #[ORM\Column(length: 50)]
    #[Groups(['news:read'])]
    #[Assert\NotBlank(message: 'Network is required')]
    #[Assert\Choice(
        choices: ['metro', 'rer', 'bus', 'tram'],
        message: 'Invalid network (allowed: metro, rer, bus, tram)'
    )]
    private ?string $network = null;

    #[ORM\Column(length: 20)]
    #[Groups(['news:read'])]
    #[Assert\NotBlank(message: 'Line is required')]
    #[Assert\Length(
        max: 20,
        maxMessage: 'Line too long (max 20 chars)'
    )]
    private ?string $line = null;

    #[ORM\Column(length: 30)]
    #[Groups(['news:read'])]
    #[Assert\NotBlank(message: 'Type is required')]
    #[Assert\Choice(
        choices: ['perturbation', 'travaux', 'incident', 'info'],
        message: 'Invalid type (allowed: perturbation, travaux, incident, info)'
    )]
    private ?string $type = null;

    #[ORM\Column(length: 20)]
    #[Groups(['news:read'])]
    #[Assert\NotBlank(message: 'Source is required')]
    #[Assert\Choice(
        choices: ['official', 'community'],
        message: 'Invalid source (allowed: official, community)'
    )]
    private ?string $source = null;

    #[ORM\Column(name: 'published_at', type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['news:read'])]
    private ?\DateTimeImmutable $publishedAt = null;

    #[ORM\Column]
    #[Groups(['news:read'])]
    private int $views = 0;

    #[ORM\ManyToOne(inversedBy: 'news')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['news:read', 'user:read'])]
    private ?User $author = null;

    #[ORM\OneToMany(mappedBy: 'news', targetEntity: Comment::class, orphanRemoval: true)]
    #[Groups(['news:read'])]
    private Collection $comments;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->views = 0;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = trim($title);
        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = trim($content);
        return $this;
    }

    public function getNetwork(): ?string
    {
        return $this->network;
    }

    public function setNetwork(string $network): static
    {
        $this->network = trim($network);
        return $this;
    }

    public function getLine(): ?string
    {
        return $this->line;
    }

    public function setLine(string $line): static
    {
        $this->line = trim($line);
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = trim($type);
        return $this;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(string $source): static
    {
        $this->source = trim($source);
        return $this;
    }

    public function getPublishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(\DateTimeImmutable $publishedAt): static
    {
        $this->publishedAt = $publishedAt;
        return $this;
    }

    public function getViews(): int
    {
        return $this->views;
    }

    public function setViews(int $views): static
    {
        $this->views = $views;
        return $this;
    }

    public function incrementViews(): static
    {
        ++$this->views;
        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;
        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setNews($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            if ($comment->getNews() === $this) {
                $comment->setNews(null);
            }
        }

        return $this;
    }
}