<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    public const STATUS_PENDING = "En cours";
    public const STATUS_PAYMENT_PROBLEM = "Paiement refusé";
    public const STATUS_PAYMENT_SUCCESSFULLY = "Paiement effectué";

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    private ?User $user = null;

    #[Assert\Email(message: 'L\'email {{ value }} n\'est pas un email valide.')]
    #[Assert\Length(
        max: 255,
        maxMessage: 'L\'email ne doit pas dépasser {{ limit }} caractères',
    )]
    #[ORM\Column(length: 255)]
    private ?string $userEmail = null;

    #[Assert\NotBlank(message:'Le prénom est obligatoire')]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Votre prénom ne peut pas contenir plus de {{ limit }} caractères',
    )]
    #[Assert\Regex(
        pattern: "/^[0-9a-zA-Z-_' áàâäãåçéèêëíìîïñóòôöõúùûüýÿæœÁÀÂÄÃÅÇÉÈÊËÍÌÎÏÑÓÒÔÖÕÚÙÛÜÝŸÆŒ]+$/i",
        match: true,
        message:"Seuls les lettres, les chiffres, l'undescore et tiret sont autorisés pour le prénom")]
    #[ORM\Column(length: 255)]
    private ?string $pickupFirstName = null;


    #[Assert\NotBlank(message:'Le nom est obligatoire')]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Votre nom ne peut pas contenir plus de {{ limit }} caractères',
    )]
    #[Assert\Regex(
        pattern: "/^[0-9a-zA-Z-_' áàâäãåçéèêëíìîïñóòôöõúùûüýÿæœÁÀÂÄÃÅÇÉÈÊËÍÌÎÏÑÓÒÔÖÕÚÙÛÜÝŸÆŒ]+$/i",
        match: true,
        message:"Seuls les lettres, les chiffres, l'undescore et tiret sont autorisés pour le nom")]
    #[ORM\Column(length: 255)]
    private ?string $pickupLastName = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $orderedAt = null;

    #[Assert\NotBlank(message:'Pour récupérer le produit il faut preciser la date')]
    #[ORM\Column]
    private ?\DateTimeImmutable $pickupDate = null;

    #[Assert\NotBlank(message:'Pour récupérer le produit il faut preciser l\'heure')]
    #[ORM\Column]
    private ?\DateTimeImmutable $pickupTime = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\Column]
    private ?float $totalAmount = null;

    /**
     * @var Collection<int, OrderDetail>
     */
    #[ORM\OneToMany(targetEntity: OrderDetail::class, mappedBy: 'theOrder', orphanRemoval: true)]
    private Collection $orderDetails;

    public function __construct()
    {
        $this->orderDetails = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getUserEmail(): ?string
    {
        return $this->userEmail;
    }

    public function setUserEmail(?string $userEmail): static
    {
        $this->userEmail = $userEmail;

        return $this;
    }

    public function getPickupFirstName(): ?string
    {
        return $this->pickupFirstName;
    }

    public function setPickupFirstName(?string $pickupFirstName): static
    {
        $this->pickupFirstName = $pickupFirstName;

        return $this;
    }

    public function getPickupLastName(): ?string
    {
        return $this->pickupLastName;
    }

    public function setPickupLastName(?string $pickupLastName): static
    {
        $this->pickupLastName = $pickupLastName;

        return $this;
    }

    public function getPickupDate(): ?\DateTimeImmutable
    {
        return $this->pickupDate;
    }

    public function setPickupDate(?\DateTimeImmutable $pickupDate): static
    {
        $this->pickupDate = $pickupDate;

        return $this;
    }

    public function getPickupTime(): ?\DateTimeImmutable
    {
        return $this->pickupTime;
    }

    public function setPickupTime(?\DateTimeImmutable $pickupTime): static
    {
        $this->pickupTime = $pickupTime;

        return $this;
    }

    public function getOrderedAt(): ?\DateTimeImmutable
    {
        return $this->orderedAt;
    }

    public function setOrderedAt(?\DateTimeImmutable $orderedAt): static
    {
        $this->orderedAt = $orderedAt;

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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getTotalAmount(): ?float
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(?float $totalAmount): static
    {
        $this->totalAmount = $totalAmount;

        return $this;
    }

    /**
     * @return Collection<int, OrderDetail>
     */
    public function getOrderDetails(): Collection
    {
        return $this->orderDetails;
    }

    public function addOrderDetail(OrderDetail $orderDetail): static
    {
        if (!$this->orderDetails->contains($orderDetail)) {
            $this->orderDetails->add($orderDetail);
            $orderDetail->setTheOrder($this);
        }

        return $this;
    }

    public function removeOrderDetail(OrderDetail $orderDetail): static
    {
        if ($this->orderDetails->removeElement($orderDetail)) {
            // set the owning side to null (unless already changed)
            if ($orderDetail->getTheOrder() === $this) {
                $orderDetail->setTheOrder(null);
            }
        }

        return $this;
    }
}
