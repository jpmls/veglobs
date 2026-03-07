<?php

namespace App\Entity\Transport;

use App\Repository\Transport\BikeStationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BikeStationRepository::class)]
#[ORM\Table(name: 'bike_station')]
#[ORM\Index(name: 'idx_bike_station_external_id', columns: ['external_id'])]
#[ORM\Index(name: 'idx_bike_station_status', columns: ['status'])]
class BikeStation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    private string $externalId;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $address = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $lat = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $lon = null;

    #[ORM\Column(nullable: true)]
    private ?bool $banking = null;

    #[ORM\Column(nullable: true)]
    private ?bool $bonus = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $status = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $contractName = null;

    #[ORM\Column(nullable: true)]
    private ?int $bikeStands = null;

    #[ORM\Column(nullable: true)]
    private ?int $availableBikeStands = null;

    #[ORM\Column(nullable: true)]
    private ?int $availableBikes = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $lastUpdate = null;

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

    public function getId(): ?int{ return $this->id; }

    public function getExternalId(): string { return $this->externalId; }
    public function setExternalId(string $externalId): self { $this->externalId = $externalId; return $this; }

    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }

    public function getAddress(): ?string { return $this->address; }
    public function setAddress(?string $address): self { $this->address = $address; return $this; }

    public function getLat(): ?float { return $this->lat; }
    public function setLat(?float $lat): self { $this->lat = $lat; return $this; }

    public function getLon(): ?float { return $this->lon; }
    public function setLon(?float $lon): self { $this->lon = $lon; return $this; }

    public function getBanking(): ?bool { return $this->banking; }
    public function setBanking(?bool $banking): self { $this->banking = $banking; return $this; }

    public function getBonus(): ?bool { return $this->bonus; }
    public function setBonus(?bool $bonus): self { $this->bonus = $bonus; return $this; }

    public function getStatus(): ?string { return $this->status; }
    public function setStatus(?string $status): self { $this->status = $status; return $this; }

    public function getContractName(): ?string { return $this->contractName; }
    public function setContractName(?string $contractName): self { $this->contractName = $contractName; return $this; }

    public function getBikeStands(): ?int { return $this->bikeStands; }
    public function setBikeStands(?int $bikeStands): self { $this->bikeStands = $bikeStands; return $this; }

    public function getAvailableBikeStands(): ?int { return $this->availableBikeStands; }
    public function setAvailableBikeStands(?int $availableBikeStands): self { $this->availableBikeStands = $availableBikeStands; return $this; }

    public function getAvailableBikes(): ?int { return $this->availableBikes; }
    public function setAvailableBikes(?int $availableBikes): self { $this->availableBikes = $availableBikes; return $this; }

    public function getLastUpdate(): ?\DateTimeImmutable { return $this->lastUpdate;

    }

    public function setLastUpdate(?\DateTimeImmutable $lastUpdate): self 
    
    { $this->lastUpdate = $lastUpdate; return $this;
    
    }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $createdAt): self { $this->createdAt = $createdAt; return $this; }

    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }
    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self { $this->updatedAt = $updatedAt; return $this; }
}