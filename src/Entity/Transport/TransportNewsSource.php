<?php

namespace App\Entity\Transport;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'transport_news_source')]
#[ORM\Index(name: 'idx_transport_news_source_external_id', columns: ['external_id'])]
class TransportNewsSource
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private string $externalId;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $lang = null;

    #[ORM\Column(length: 255)]
    private string $title;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $type = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $linkType = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $link = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $titlePage = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $textPage = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $buttonText = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $createdSourceAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedSourceAt = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $rawHtml = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    public function getId(): ?int { return $this->id; }

    public function getExternalId(): string { return $this->externalId; }
    public function setExternalId(string $externalId): self { $this->externalId = $externalId; return $this; }

    public function getLang(): ?string { return $this->lang; }
    public function setLang(?string $lang): self { $this->lang = $lang; return $this; }

    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): self { $this->title = $title; return $this; }

    public function getType(): ?string { return $this->type; }
    public function setType(?string $type): self { $this->type = $type; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): self { $this->description = $description; return $this; }

    public function getLinkType(): ?string { return $this->linkType; }
    public function setLinkType(?string $linkType): self { $this->linkType = $linkType; return $this; }

    public function getLink(): ?string { return $this->link; }
    public function setLink(?string $link): self { $this->link = $link; return $this; }

    public function getTitlePage(): ?string { return $this->titlePage; }
    public function setTitlePage(?string $titlePage): self { $this->titlePage = $titlePage; return $this; }

    public function getTextPage(): ?string { return $this->textPage; }
    public function setTextPage(?string $textPage): self { $this->textPage = $textPage; return $this; }

    public function getButtonText(): ?string { return $this->buttonText; }
    public function setButtonText(?string $buttonText): self { $this->buttonText = $buttonText; return $this; }

    public function getCreatedSourceAt(): ?\DateTimeImmutable { return $this->createdSourceAt; }
    public function setCreatedSourceAt(?\DateTimeImmutable $createdSourceAt): self { $this->createdSourceAt = $createdSourceAt; return $this; }

    public function getUpdatedSourceAt(): ?\DateTimeImmutable { return $this->updatedSourceAt; }
    public function setUpdatedSourceAt(?\DateTimeImmutable $updatedSourceAt): self { $this->updatedSourceAt = $updatedSourceAt; return $this; }

    public function getRawHtml(): ?string { return $this->rawHtml; }
    public function setRawHtml(?string $rawHtml): self { $this->rawHtml = $rawHtml; return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $createdAt): self { $this->createdAt = $createdAt; return $this; }

    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }
    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self { $this->updatedAt = $updatedAt; return $this; }
}