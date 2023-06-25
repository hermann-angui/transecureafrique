<?php


namespace App\Entity;

use App\Repository\DemandeRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: DemandeRepository::class)]
#[ORM\Table(name: '`demande`')]
#[ORM\HasLifecycleCallbacks()]
class Demande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $numero_carte_grise;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $numero_recepisse;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $numero_immatriculation;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTime  $date_de_premiere_mise_en_cirulation;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTime  $date_d_edition;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $identite_proprietaire;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $identite_proprietaire_piece;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string  $marque_du_vehicule;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $genre_vehicule;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string  $type_commercial;

    #[ORM\Column(type: 'string', length: 255,  nullable: true)]
    private ?string $couleur_vehicule;

    #[ORM\Column(type: 'string', length: 255,  nullable: true)]
    private ?string $carroserie_vehicule;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $energie_vehicule;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $places_assises;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $usage_vehicule;

    #[ORM\Column(type: 'string', length: 255,  nullable: true)]
    private ?string  $puissance_fiscale;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string  $nombre_d_essieux;

    #[ORM\Column(type: 'string', length: 255,  nullable: true)]
    private ?string $cylindree;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string  $numero_vin_chassis;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string  $societe_de_credit;

    #[ORM\Column(type: 'string', length: 255,  nullable: true)]
    private ?string $type_technique;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string  $numero_d_immatriculation_precedent;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $reference;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $montant;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $status;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private DateTime $date_rendez_vous;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private DateTime $created_at;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private DateTime $modified_at;

    #[ORM\OneToMany(mappedBy: 'demande', targetEntity: OtpCode::class)]
    private Collection $otpCodes;

    #[ORM\OneToOne(inversedBy: 'demande', cascade: ['persist', 'remove'])]
    private ?Payment $payment = null;

    #[ORM\OneToOne(mappedBy: 'demande', cascade: ['persist', 'remove'])]
    private ?Macaron $macaron = null;


    public function __construct()
    {
        $this->created_at = new \DateTime();
        $this->modified_at = new \DateTime();
        $this->otpCodes = new ArrayCollection();
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
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     * @return User
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNumeroCarteGrise(): ?string
    {
        return $this->numero_carte_grise;
    }

    /**
     * @param string|null $numero_carte_grise
     * @return Demande
     */
    public function setNumeroCarteGrise(?string $numero_carte_grise): Demande
    {
        $this->numero_carte_grise = $numero_carte_grise;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNumeroImmatriculation(): ?string
    {
        return $this->numero_immatriculation;
    }

    /**
     * @param string|null $numero_immatriculation
     * @return Demande
     */
    public function setNumeroImmatriculation(?string $numero_immatriculation): Demande
    {
        $this->numero_immatriculation = $numero_immatriculation;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getDateDePremiereMiseEnCirulation(): ?DateTime
    {
        return $this->date_de_premiere_mise_en_cirulation;
    }

    /**
     * @param DateTime|null $date_de_premiere_mise_en_cirulation
     * @return Demande
     */
    public function setDateDePremiereMiseEnCirulation(?DateTime $date_de_premiere_mise_en_cirulation): Demande
    {
        $this->date_de_premiere_mise_en_cirulation = $date_de_premiere_mise_en_cirulation;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getDateDEdition(): ?DateTime
    {
        return $this->date_d_edition;
    }

    /**
     * @param DateTime|null $date_d_edition
     * @return Demande
     */
    public function setDateDEdition(?DateTime $date_d_edition): Demande
    {
        $this->date_d_edition = $date_d_edition;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getIdentiteProprietaire(): ?string
    {
        return $this->identite_proprietaire;
    }

    /**
     * @param string|null $identite_proprietaire
     * @return Demande
     */
    public function setIdentiteProprietaire(?string $identite_proprietaire): Demande
    {
        $this->identite_proprietaire = $identite_proprietaire;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getIdentiteProprietairePiece(): ?string
    {
        return $this->identite_proprietaire_piece;
    }

    /**
     * @param string|null $identite_proprietaire_piece
     * @return Demande
     */
    public function setIdentiteProprietairePiece(?string $identite_proprietaire_piece): Demande
    {
        $this->identite_proprietaire_piece = $identite_proprietaire_piece;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMarqueDuVehicule(): ?string
    {
        return $this->marque_du_vehicule;
    }

    /**
     * @param string|null $marque_du_vehicule
     * @return Demande
     */
    public function setMarqueDuVehicule(?string $marque_du_vehicule): Demande
    {
        $this->marque_du_vehicule = $marque_du_vehicule;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getGenreVehicule(): ?string
    {
        return $this->genre_vehicule;
    }

    /**
     * @param string|null $genre_vehicule
     * @return Demande
     */
    public function setGenreVehicule(?string $genre_vehicule): Demande
    {
        $this->genre_vehicule = $genre_vehicule;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTypeCommercial(): ?string
    {
        return $this->type_commercial;
    }

    /**
     * @param string|null $type_commercial
     * @return Demande
     */
    public function setTypeCommercial(?string $type_commercial): Demande
    {
        $this->type_commercial = $type_commercial;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCouleurVehicule(): ?string
    {
        return $this->couleur_vehicule;
    }

    /**
     * @param string|null $couleur_vehicule
     * @return Demande
     */
    public function setCouleurVehicule(?string $couleur_vehicule): Demande
    {
        $this->couleur_vehicule = $couleur_vehicule;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCarroserieVehicule(): ?string
    {
        return $this->carroserie_vehicule;
    }

    /**
     * @param string|null $carroserie_vehicule
     * @return Demande
     */
    public function setCarroserieVehicule(?string $carroserie_vehicule): Demande
    {
        $this->carroserie_vehicule = $carroserie_vehicule;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getEnergieVehicule(): ?string
    {
        return $this->energie_vehicule;
    }

    /**
     * @param string|null $energie_vehicule
     * @return Demande
     */
    public function setEnergieVehicule(?string $energie_vehicule): Demande
    {
        $this->energie_vehicule = $energie_vehicule;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPlacesAssises(): ?string
    {
        return $this->places_assises;
    }

    /**
     * @param string|null $places_assises
     * @return Demande
     */
    public function setPlacesAssises(?string $places_assises): Demande
    {
        $this->places_assises = $places_assises;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUsageVehicule(): ?string
    {
        return $this->usage_vehicule;
    }

    /**
     * @param string|null $usage_vehicule
     * @return Demande
     */
    public function setUsageVehicule(?string $usage_vehicule): Demande
    {
        $this->usage_vehicule = $usage_vehicule;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPuissanceFiscale(): ?string
    {
        return $this->puissance_fiscale;
    }

    /**
     * @param string|null $puissance_fiscale
     * @return Demande
     */
    public function setPuissanceFiscale(?string $puissance_fiscale): Demande
    {
        $this->puissance_fiscale = $puissance_fiscale;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNombreDEssieux(): ?string
    {
        return $this->nombre_d_essieux;
    }

    /**
     * @param string|null $nombre_d_essieux
     * @return Demande
     */
    public function setNombreDEssieux(?string $nombre_d_essieux): Demande
    {
        $this->nombre_d_essieux = $nombre_d_essieux;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCylindree(): ?string
    {
        return $this->cylindree;
    }

    /**
     * @param string|null $cylindree
     * @return Demande
     */
    public function setCylindree(?string $cylindree): Demande
    {
        $this->cylindree = $cylindree;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNumeroVinChassis(): ?string
    {
        return $this->numero_vin_chassis;
    }

    /**
     * @param string|null $numero_vin_chassis
     * @return Demande
     */
    public function setNumeroVinChassis(?string $numero_vin_chassis): Demande
    {
        $this->numero_vin_chassis = $numero_vin_chassis;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSocieteDeCredit(): ?string
    {
        return $this->societe_de_credit;
    }

    /**
     * @param string|null $societe_de_credit
     * @return Demande
     */
    public function setSocieteDeCredit(?string $societe_de_credit): Demande
    {
        $this->societe_de_credit = $societe_de_credit;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTypeTechnique(): ?string
    {
        return $this->type_technique;
    }

    /**
     * @param string|null $type_technique
     * @return Demande
     */
    public function setTypeTechnique(?string $type_technique): Demande
    {
        $this->type_technique = $type_technique;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNumeroDImmatriculationPrecedent(): ?string
    {
        return $this->numero_d_immatriculation_precedent;
    }

    /**
     * @param string|null $numero_d_immatriculation_precedent
     * @return Demande
     */
    public function setNumeroDImmatriculationPrecedent(?string $numero_d_immatriculation_precedent): Demande
    {
        $this->numero_d_immatriculation_precedent = $numero_d_immatriculation_precedent;
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
     * @return Demande
     */
    public function setReference(?string $reference): Demande
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
     * @return Demande
     */
    public function setMontant(?int $montant): Demande
    {
        $this->montant = $montant;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNumeroRecepisse(): ?string
    {
        return $this->numero_recepisse;
    }

    /**
     * @param string|null $numero_recepisse
     * @return Demande
     */
    public function setNumeroRecepisse(?string $numero_recepisse): Demande
    {
        $this->numero_recepisse = $numero_recepisse;
        return $this;
    }

    /**
     * @return Date
     */
    public function getDateRendezVous(): \DateTime
    {
        return $this->date_rendez_vous;
    }

    /**
     * @param DateTime $date_rendez_vous
     * @return Demande
     */
    public function setDateRendezVous(?\DateTime $date_rendez_vous): Demande
    {
        $this->date_rendez_vous = $date_rendez_vous;
        return $this;
    }

    /**
     * @return Collection<int, OtpCode>
     */
    public function getOtpCodes(): Collection
    {
        return $this->otpCodes;
    }

    public function addOtpCode(OtpCode $otpCode): self
    {
        if (!$this->otpCodes->contains($otpCode)) {
            $this->otpCodes[] = $otpCode;
            $otpCode->setDemande($this);
        }

        return $this;
    }

    public function removeOtpCode(OtpCode $otpCode): self
    {
        if ($this->otpCodes->removeElement($otpCode)) {
            // set the owning side to null (unless already changed)
            if ($otpCode->getDemande() === $this) {
                $otpCode->setDemande(null);
            }
        }

        return $this;
    }

    public function getPayment(): ?Payment
    {
        return $this->payment;
    }

    public function setPayment(?Payment $payment): self
    {
        $this->payment = $payment;

        return $this;
    }

    public function getMacaron(): ?Macaron
    {
        return $this->macaron;
    }

    public function setMacaron(?Macaron $macaron): self
    {
        // unset the owning side of the relation if necessary
        if ($macaron === null && $this->macaron !== null) {
            $this->macaron->setDemande(null);
        }

        // set the owning side of the relation if necessary
        if ($macaron !== null && $macaron->getDemande() !== $this) {
            $macaron->setDemande($this);
        }

        $this->macaron = $macaron;

        return $this;
    }

}
