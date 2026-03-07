<?php

namespace App\Entity\Transport;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'transport_stop_relation')]
#[ORM\Index(name: 'idx_transport_stop_relation_zdc', columns: ['zdc_id'])]
#[ORM\Index(name: 'idx_transport_stop_relation_zda', columns: ['zda_id'])]
#[ORM\Index(name: 'idx_transport_stop_relation_arr', columns: ['arr_id'])]
class TransportStopRelation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $pdeId = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $pdeVersion = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $zdcId = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $zdcVersion = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $zdaId = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $zdaVersion = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $arrId = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $arrVersion = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $artId = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $artVersion = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $arrLat = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $arrLon = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $artLat = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $artLon = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int {
        
    return $this->id; 
    
    }

    public function getPdeId(): ?string { 
        
    return $this->pdeId; 
    
    }
    public function setPdeId(?string $pdeId): self { 
        
    $this->pdeId = $pdeId; return $this; 
    
    }

    public function getPdeVersion(): ?string {
        
    return $this->pdeVersion; 
    
    }
    public function setPdeVersion(?string $pdeVersion): self { 
        
    $this->pdeVersion = $pdeVersion; return $this; 
    
    }

    public function getZdcId(): ?string { 
        
    return $this->zdcId; 
    
    }
    public function setZdcId(?string $zdcId): self { 
        
    $this->zdcId = $zdcId; return $this; 
    
    }

    public function getZdcVersion(): ?string { 
        
    return $this->zdcVersion;
    
    }
    public function setZdcVersion(?string $zdcVersion): self { 
        
    $this->zdcVersion = $zdcVersion; return $this; 
    
    }

    public function getZdaId(): ?string { 
        
    return $this->zdaId; 
    
    }
    public function setZdaId(?string $zdaId): self { $this->zdaId = $zdaId; return $this; }

    public function getZdaVersion(): ?string { return $this->zdaVersion; }
    public function setZdaVersion(?string $zdaVersion): self { $this->zdaVersion = $zdaVersion; return $this; }

    public function getArrId(): ?string { 
        
    return $this->arrId; 
    
    }
    public function setArrId(?string $arrId): self { 
        
    $this->arrId = $arrId; return $this;
    
    }

    public function getArrVersion(): ?string { 
        
    return $this->arrVersion; 
    
    }
    public function setArrVersion(?string $arrVersion): self {
        
    $this->arrVersion = $arrVersion; return $this;
    
    }

    public function getArtId(): ?string { 
        
    return $this->artId; 
    
    }
    public function setArtId(?string $artId): self { 
        
    $this->artId = $artId; return $this; 
    
    }

    public function getArtVersion(): ?string {
        
    return $this->artVersion; 
    
    }
    public function setArtVersion(?string $artVersion): self {
        
    $this->artVersion = $artVersion; return $this; 
    
    }

    public function getArrLat(): ?float { return $this->arrLat; }
    public function setArrLat(?float $arrLat): self { $this->arrLat = $arrLat; return $this; }

    public function getArrLon(): ?float { return $this->arrLon; }
    public function setArrLon(?float $arrLon): self { $this->arrLon = $arrLon; return $this; }

    public function getArtLat(): ?float { return $this->artLat; }
    public function setArtLat(?float $artLat): self { $this->artLat = $artLat; return $this; }

    public function getArtLon(): ?float { return $this->artLon; }
    public function setArtLon(?float $artLon): self { $this->artLon = $artLon; return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}