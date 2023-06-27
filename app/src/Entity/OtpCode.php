<?php


namespace App\Entity;

use App\Repository\OtpCodeRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: OtpCodeRepository::class)]
#[ORM\Table(name: '`otpcode`')]
#[ORM\HasLifecycleCallbacks()]
class OtpCode
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $code;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $phone;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $webservice_reference;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private bool $is_expired;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private DateTime $created_at;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private DateTime $modified_at;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private DateTime $expired_at;

    #[ORM\ManyToOne(inversedBy: 'otpCode')]
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
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @param string|null $code
     * @return OtpCode
     */
    public function setCode(?string $code): OtpCode
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @param string|null $phone
     * @return OtpCode
     */
    public function setPhone(?string $phone): OtpCode
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsExpired(): ?bool
    {
        return $this->is_expired;
    }

    /**
     * @param bool $is_expired
     * @return OtpCode
     */
    public function setIsExpired(?bool $is_expired)
    {
        $this->is_expired = $is_expired;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getExpiredAt(): DateTime
    {
        return $this->expired_at;
    }

    /**
     * @param DateTime $expired_at
     * @return OtpCode
     */
    public function setExpiredAt(DateTime $expired_at): OtpCode
    {
        $this->expired_at = $expired_at;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getWebserviceReference(): ?string
    {
        return $this->webservice_reference;
    }

    /**
     * @param string|null $webservice_reference
     * @return OtpCode
     */
    public function setWebserviceReference(?string $webservice_reference): OtpCode
    {
        $this->webservice_reference = $webservice_reference;
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
}
