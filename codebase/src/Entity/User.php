<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\HasLifecycleCallbacks()]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255, unique: true, nullable: true)]
    private ?string  $email;

    #[ORM\Column(type: 'json')]
    private $roles = [];

    #[ORM\Column(type: 'string')]
    private ?string  $password;

    private  ?string $plain_password;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string  $firstname;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string  $lastname;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $photo;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $created_at;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $modified_at;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $last_connection;

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
        $this->modified_at =  new \DateTime('now');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
       $this->plain_password = null;
    }

    /**
     * @return mixed
     */
    public function getPlainPassword()
    {
        return $this->plain_password;
    }

    /**
     * @param mixed $plain_password
     * @return User
     */
    public function setPlainPassword($plain_password)
    {
        $this->plain_password = $plain_password;
        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->created_at;
    }

    public function setCreatedAt(?\DateTime $createAt): self
    {
        $this->created_at = $createAt;

        return $this;
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    /**
     * @param string|null $firstname
     * @return User
     */
    public function setFirstname(?string $firstname): User
    {
        $this->firstname = $firstname;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    /**
     * @param string|null $lastname
     * @return User
     */
    public function setLastname(?string $lastname): User
    {
        $this->lastname = $lastname;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastConnection(): ?\DateTime
    {
        return $this->last_connection;
    }

    /**
     * @param \DateTime|null $last_connection
     * @return User
     */
    public function setLastConnection(?\DateTime $last_connection): User
    {
        $this->last_connection = $last_connection;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    /**
     * @param string|null $photo
     * @return User
     */
    public function setPhoto(?string $photo): User
    {
        $this->photo = $photo;
        return $this;
    }


}
