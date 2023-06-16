<?php

namespace App\Entity;

use App\Repository\MacaronRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


#[ORM\Entity(repositoryClass: MacaronRepository::class)]
#[ORM\Table(name: '`macaron`')]
#[UniqueEntity(fields: ['numero'], message: 'There is already an account with this email')]
class Macaron
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $reference;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $macaron_qrcode_image;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $macaron_qrcode_number;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $status;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $validity_from;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $validity_to;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $created_at;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $modified_at;

    #[ORM\OneToOne(inversedBy: 'macaron', cascade: ['remove'])]
    private ?Demande $demande = null;

    public function __construct()
    {
        $this->created_at = new \DateTime();
        $this->modified_at = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    /**
     * @param \DateTimeInterface|null $created_at
     * @return Macaron
     */
    public function setCreatedAt(?\DateTimeInterface $created_at): Macaron
    {
        $this->created_at = $created_at;
        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getModifiedAt(): ?\DateTimeInterface
    {
        return $this->modified_at;
    }

    /**
     * @param \DateTimeInterface|null $modified_at
     * @return Macaron
     */
    public function setModifiedAt(?\DateTimeInterface $modified_at): Macaron
    {
        $this->modified_at = $modified_at;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getReference(): ?string
    {
        return $this->reference;
    }

    /**
     * @param string|null $reference
     * @return Macaron
     */
    public function setReference(?string $reference): Macaron
    {
        $this->reference = $reference;
        return $this;
    }

    public function getDemande(): ?Demande
    {
        return $this->demande;
    }

    public function setDemande(?Demande $demande): self
    {
        $this->demande = $demande;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getMacaronQrcodeImage(): ?string
    {
        return $this->macaron_qrcode_image;
    }

    /**
     * @param string|null $macaron_qrcode_image
     * @return Macaron
     */
    public function setMacaronQrcodeImage(?string $macaron_qrcode_image): Macaron
    {
        $this->macaron_qrcode_image = $macaron_qrcode_image;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMacaronQrcodeNumber(): ?string
    {
        return $this->macaron_qrcode_number;
    }

    /**
     * @param string|null $macaron_qrcode_number
     * @return Macaron
     */
    public function setMacaronQrcodeNumber(?string $macaron_qrcode_number): Macaron
    {
        $this->macaron_qrcode_number = $macaron_qrcode_number;
        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getValidityFrom(): ?\DateTimeInterface
    {
        return $this->validity_from;
    }

    /**
     * @param \DateTimeInterface|null $validity_from
     * @return Macaron
     */
    public function setValidityFrom(?\DateTimeInterface $validity_from): Macaron
    {
        $this->validity_from = $validity_from;
        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getValidityTo(): ?\DateTimeInterface
    {
        return $this->validity_to;
    }

    /**
     * @param \DateTimeInterface|null $validity_to
     * @return Macaron
     */
    public function setValidityTo(?\DateTimeInterface $validity_to): Macaron
    {
        $this->validity_to = $validity_to;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @param string|null $status
     * @return Macaron
     */
    public function setStatus(?string $status): Macaron
    {
        $this->status = $status;
        return $this;
    }


}
