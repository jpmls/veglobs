<?php

namespace App\Entity\Transport;

use App\Repository\Transport\TransportStopRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransportStopRepository::class)]
#[ORM\Table(name: 'transport_stop')]
#[ORM\Index(name: 'idx_transport_stop_external_id', columns: ['external_id'])]
#[ORM\Index(name: 'idx_transport_stop_name', columns: ['name'])]
class TransportStop
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    private string $externalId;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $version = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $createdSourceAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $changedSourceAt = null;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $stopType = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $xEpsg2154 = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $yEpsg2154 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $town = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $postalRegion = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $accessibility = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $audibleSignals = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $visualSigns = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $fareZone = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $zdaExternalId = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $lat = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $lon = null;

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

    public function getVersion(): ?string { return $this->version; }
    public function setVersion(?string $version): self { $this->version = $version; return $this; }

    public function getCreatedSourceAt(): ?\DateTimeImmutable { return $this->createdSourceAt; }
    public function setCreatedSourceAt(?\DateTimeImmutable $createdSourceAt): self { $this->createdSourceAt = $createdSourceAt; return $this; }

    public function getChangedSourceAt(): ?\DateTimeImmutable { return $this->changedSourceAt; }
    public function setChangedSourceAt(?\DateTimeImmutable $changedSourceAt): self { $this->changedSourceAt = $changedSourceAt; return $this; }

    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }

    public function getStopType(): ?string { return $this->stopType; }
    public function setStopType(?string $stopType): self { $this->stopType = $stopType; return $this; }

    public function getXEpsg2154(): ?string { return $this->xEpsg2154; }
    public function setXEpsg2154(?string $xEpsg2154): self { $this->xEpsg2154 = $xEpsg2154; return $this; }

    public function getYEpsg2154(): ?string { return $this->yEpsg2154; }
    public function setYEpsg2154(?string $yEpsg2154): self { $this->yEpsg2154 = $yEpsg2154; return $this; }

    public function getTown(): ?string { return $this->town; }
    public function setTown(?string $town): self { $this->town = $town; return $this; }

    public function getPostalRegion(): ?string { return $this->postalRegion; }
    public function setPostalRegion(?string $postalRegion): self { $this->postalRegion = $postalRegion; return $this; }

    public function getAccessibility(): ?string { return $this->accessibility; }
    public function setAccessibility(?string $accessibility): self { $this->accessibility = $accessibility; return $this; }

    public function getAudibleSignals(): ?string { return $this->audibleSignals; }
    public function setAudibleSignals(?string $audibleSignals): self { $this->audibleSignals = $audibleSignals; return $this; }

    public function getVisualSigns(): ?string { return $this->visualSigns; }
    public function setVisualSigns(?string $visualSigns): self { $this->visualSigns = $visualSigns; return $this; }

    public function getFareZone(): ?string { return $this->fareZone; }
    public function setFareZone(?string $fareZone): self { $this->fareZone = $fareZone; return $this; }

    public function getZdaExternalId(): ?string { return $this->zdaExternalId; }
    public function setZdaExternalId(?string $zdaExternalId): self { $this->zdaExternalId = $zdaExternalId; return $this; }

    public function getLat(): ?float { return $this->lat; }
    public function setLat(?float $lat): self { $this->lat = $lat; return $this; }

    public function getLon(): ?float { return $this->lon; }
    public function setLon(?float $lon): self { $this->lon = $lon; return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $createdAt): self { $this->createdAt = $createdAt; return $this; }

    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }
    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self { $this->updatedAt = $updatedAt; return $this; }
}