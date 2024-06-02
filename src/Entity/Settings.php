<?php

namespace App\Entity;

use App\Repository\SettingsRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: SettingsRepository::class)]
class Settings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\Length(
        max: 255,
        maxMessage: 'Le nom du site ne doit pas depasser de {{ limit }} caractères',
    )]
    #[Assert\NotBlank(message: 'Le nom du site est obligatoire')]
    #[ORM\Column(length: 255)]
    private ?string $websiteName = null;
    
    #[Assert\Length(
        max: 255,
        maxMessage: 'L\'Url du site ne doit pas depasser de {{ limit }} caractères',
    )]
    #[Assert\NotBlank(message: 'L\'URL du site est obligatoire')]
    #[Assert\Url(message: 'L\'Url n\'est pas correcte')]
    #[ORM\Column(length: 255)]
    private ?string $websiteUrl = null;

    #[Assert\NotBlank(message: 'La description est obligatoire')]
    #[Assert\Length(
        max: 600,
        maxMessage: 'La description ne peut pas contenir plus de {{ limit }} caractères',
    )]
    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[Assert\NotBlank(message:'L\'email est obligatoire')]
    #[Assert\Email(message: 'L\'email {{ value }} n\'est pas un email valide.')]
    #[Assert\Length(
        max: 255,
        maxMessage: 'L\'email ne doit pas dépasser {{ limit }} caractères',
    )]
    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[Assert\Length(
        min: 6,
        max: 255,
        minMessage: "Le numéro de téléphone doit contenir au minimum {{ limit }} caractères.",
        maxMessage: "Le numéro de téléphone doit contenir au maximum {{ limit }} caractères.",
    )]
    #[Assert\Regex(
        pattern: '/^[0-9\s\-\+\(\)]+$/',
        match: true,
        message: "Le numéro de téléphone est invalide.",
    )]
    #[ORM\Column(length: 255)]
    private ?string $phone = null;

    #[Assert\Length(
        max: 255,
        maxMessage: 'L\'adresse ne doit pas depasser de {{ limit }} caractères',
    )]
    #[Assert\NotBlank(message: 'L\'adresse est obligatoire')]
    #[ORM\Column(length: 255)]
    private ?string $adresse = null;

    #[Assert\Length(
        max: 255,
        maxMessage: 'La ville ne doit pas depasser de {{ limit }} caractères',
    )]
    #[Assert\NotBlank(message: 'La ville est obligatoire')]
    #[ORM\Column(length: 255)]
    private ?string $city = null;
    
    #[Assert\Length(
        max: 255,
        maxMessage: 'Le pays ne doit pas depasser de {{ limit }} caractères',
    )]
    #[Assert\Country(message: 'Ce pays est inconnu')]
    #[Assert\NotBlank(message: 'Le pays est obligatoire')]
    #[ORM\Column(length: 255)]
    private ?string $country = null;

    #[Assert\Regex(
        pattern: '/^[0-9]+$/',
        match: true,
        message: "Le code postal est invalide.",
    )]
    #[Assert\Length(
        min: 5,
        max: 10,
        minMessage: 'Le code postal doit avoir au moins {{ limit }} caractères',
        maxMessage: 'Le code postal ne doit pas depasser de {{ limit }} caractères',
    )]
    #[Assert\NotBlank(message: 'Le code postal est obligatoire')]
    #[ORM\Column(length: 255)]
    private ?string $postalCode = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne]
    private ?User $user = null;



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWebsiteName(): ?string
    {
        return $this->websiteName;
    }

    public function setWebsiteName(?string $websiteName): static
    {
        $this->websiteName = $websiteName;

        return $this;
    }

    public function getWebsiteUrl(): ?string
    {
        return $this->websiteUrl;
    }

    public function setWebsiteUrl(?string $websiteUrl): static
    {
        $this->websiteUrl = $websiteUrl;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): static
    {
        $this->city = $city;

        return $this;
    }
    
    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): static
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
