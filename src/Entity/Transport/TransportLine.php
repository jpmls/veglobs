<?php

namespace App\Entity\Transport;

use App\Repository\Transport\TransportLineRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransportLineRepository::class)]
#[ORM\Table(name: 'transport_line')]
#[ORM\Index(name: 'idx_transport_line_external_id', columns: ['external_id'])]
#[ORM\Index(name: 'idx_transport_line_mode', columns: ['transport_mode'])]
class TransportLine
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    private string $externalId;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $shortName = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $transportMode = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $transportSubmode = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $type = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $operatorRef = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $operatorName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $additionalOperatorsRef = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $networkName = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $colorHex = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $textColorHex = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $colorPrintCmjn = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $textColorPrintHex = null;

    #[ORM\Column(nullable: true)]
    private ?bool $accessibility = null;

    #[ORM\Column(nullable: true)]
    private ?bool $audibleSignsAvailable = null;

    #[ORM\Column(nullable: true)]
    private ?bool $visualSignsAvailable = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $groupExternalId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $groupShortName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $noticeTitle = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $noticeText = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $picto = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $validFromDate = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $validToDate = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $status = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $privateCode = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $airConditioning = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $busContractId = null;

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

    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }

    public function getShortName(): ?string { return $this->shortName; }
    public function setShortName(?string $shortName): self { $this->shortName = $shortName; return $this; }

    public function getTransportMode(): ?string { return $this->transportMode; }
    public function setTransportMode(?string $transportMode): self { $this->transportMode = $transportMode; return $this; }

    public function getTransportSubmode(): ?string { return $this->transportSubmode; }
    public function setTransportSubmode(?string $transportSubmode): self { $this->transportSubmode = $transportSubmode; return $this; }

    public function getType(): ?string { return $this->type; }
    public function setType(?string $type): self { $this->type = $type; return $this; }

    public function getOperatorRef(): ?string { return $this->operatorRef; }
    public function setOperatorRef(?string $operatorRef): self { $this->operatorRef = $operatorRef; return $this; }

    public function getOperatorName(): ?string { return $this->operatorName; }
    public function setOperatorName(?string $operatorName): self { $this->operatorName = $operatorName; return $this; }

    public function getAdditionalOperatorsRef(): ?string { return $this->additionalOperatorsRef; }
    public function setAdditionalOperatorsRef(?string $additionalOperatorsRef): self { $this->additionalOperatorsRef = $additionalOperatorsRef; return $this; }

    public function getNetworkName(): ?string { return $this->networkName; }
    public function setNetworkName(?string $networkName): self { $this->networkName = $networkName; return $this; }

    public function getColorHex(): ?string { return $this->colorHex; }
    public function setColorHex(?string $colorHex): self { $this->colorHex = $colorHex; return $this; }

    public function getTextColorHex(): ?string { return $this->textColorHex; }
    public function setTextColorHex(?string $textColorHex): self { $this->textColorHex = $textColorHex; return $this; }

    public function getColorPrintCmjn(): ?string { return $this->colorPrintCmjn; }
    public function setColorPrintCmjn(?string $colorPrintCmjn): self { $this->colorPrintCmjn = $colorPrintCmjn; return $this; }

    public function getTextColorPrintHex(): ?string { return $this->textColorPrintHex; }
    public function setTextColorPrintHex(?string $textColorPrintHex): self { $this->textColorPrintHex = $textColorPrintHex; return $this; }

    public function getAccessibility(): ?bool { return $this->accessibility; }
    public function setAccessibility(?bool $accessibility): self { $this->accessibility = $accessibility; return $this; }

    public function getAudibleSignsAvailable(): ?bool { return $this->audibleSignsAvailable; }
    public function setAudibleSignsAvailable(?bool $audibleSignsAvailable): self { $this->audibleSignsAvailable = $audibleSignsAvailable; return $this; }

    public function getVisualSignsAvailable(): ?bool { return $this->visualSignsAvailable; }
    public function setVisualSignsAvailable(?bool $visualSignsAvailable): self { $this->visualSignsAvailable = $visualSignsAvailable; return $this; }

    public function getGroupExternalId(): ?string { return $this->groupExternalId; }
    public function setGroupExternalId(?string $groupExternalId): self { $this->groupExternalId = $groupExternalId; return $this; }

    public function getGroupShortName(): ?string { return $this->groupShortName; }
    public function setGroupShortName(?string $groupShortName): self { $this->groupShortName = $groupShortName; return $this; }

    public function getNoticeTitle(): ?string { return $this->noticeTitle; }
    public function setNoticeTitle(?string $noticeTitle): self { $this->noticeTitle = $noticeTitle; return $this; }

    public function getNoticeText(): ?string { return $this->noticeText; }
    public function setNoticeText(?string $noticeText): self { $this->noticeText = $noticeText; return $this; }

    public function getPicto(): ?string { return $this->picto; }
    public function setPicto(?string $picto): self { $this->picto = $picto; return $this; }

    public function getValidFromDate(): ?\DateTimeImmutable { return $this->validFromDate; }
    public function setValidFromDate(?\DateTimeImmutable $validFromDate): self { $this->validFromDate = $validFromDate; return $this; }

    public function getValidToDate(): ?\DateTimeImmutable { return $this->validToDate; }
    public function setValidToDate(?\DateTimeImmutable $validToDate): self { $this->validToDate = $validToDate; return $this; }

    public function getStatus(): ?string { return $this->status; }
    public function setStatus(?string $status): self { $this->status = $status; return $this; }

    public function getPrivateCode(): ?string { return $this->privateCode; }
    public function setPrivateCode(?string $privateCode): self { $this->privateCode = $privateCode; return $this; }

    public function getAirConditioning(): ?string { return $this->airConditioning; }
    public function setAirConditioning(?string $airConditioning): self { $this->airConditioning = $airConditioning; return $this; }

    public function getBusContractId(): ?string { return $this->busContractId; }
    public function setBusContractId(?string $busContractId): self { $this->busContractId = $busContractId; return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $createdAt): self { $this->createdAt = $createdAt; return $this; }

    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }
    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self { $this->updatedAt = $updatedAt; return $this; }
}