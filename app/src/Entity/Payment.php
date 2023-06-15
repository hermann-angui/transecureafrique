<?php


namespace App\Entity;

use App\Repository\PaymentRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: PaymentRepository::class)]
#[ORM\Table(name: '`payment`')]
#[ORM\HasLifecycleCallbacks()]
class Payment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $reference;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $montant;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $paiement_type = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $status;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $operateur;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $code_paiement_operateur;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private DateTime $created_at;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private DateTime $modified_at;

    #[ORM\OneToOne(inversedBy: 'payment', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Demande $demande = null;

    public function __construct()
    {
        $this->created_at = new \DateTime();
        $this->modified_at = new \DateTime();
    }

    /**
     * Prepersist gets triggered on Insert
     * @ORM\PrePersist
     */
    public function updatedTimestamps()
    {
        if ($this->created_at == null) {
            $this->created_at = new \DateTime('now');
        }
        $this->modified_at = new \DateTime('now');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setCreatedAt(?\DateTime $createAt): self
    {
        $this->created_at = $createAt;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->created_at;
    }

    public function setModifiedAt(?\DateTime $modified_at): self
    {
        $this->modified_at = $modified_at;

        return $this;
    }

    public function getModifiedAt(): ?\DateTime
    {
        return $this->modified_at;
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
     * @return Payment
     */
    public function setReference(?string $reference): Payment
    {
        $this->reference = $reference;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getMontant(): ?int
    {
        return $this->montant;
    }

    /**
     * @param int|null $montant
     * @return Payment
     */
    public function setMontant(?int $montant): Payment
    {
        $this->montant = $montant;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPaiementType(): ?string
    {
        return $this->paiement_type;
    }

    /**
     * @param string|null $paiement_type
     * @return Payment
     */
    public function setPaiementType(?string $paiement_type): Payment
    {
        $this->paiement_type = $paiement_type;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     * @return Payment
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOperateur()
    {
        return $this->operateur;
    }

    /**
     * @param mixed $operateur
     * @return Payment
     */
    public function setOperateur($operateur)
    {
        $this->operateur = $operateur;
        return $this;
    }

    public function getDemande(): ?Demande
    {
        return $this->demande;
    }

    public function setDemande(Demande $demande): self
    {
        $this->demande = $demande;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCodePaiementOperateur()
    {
        return $this->code_paiement_operateur;
    }

    /**
     * @param mixed $code_paiement_operateur
     * @return Payment
     */
    public function setCodePaiementOperateur($code_paiement_operateur)
    {
        $this->code_paiement_operateur = $code_paiement_operateur;
        return $this;
    }


}
