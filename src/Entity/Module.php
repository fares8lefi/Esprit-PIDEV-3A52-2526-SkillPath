<?php

namespace App\Entity;

use App\Repository\ModuleRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\Cours;

#[ORM\Entity(repositoryClass: ModuleRepository::class)]
class Module
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    #[Assert\NotBlank(message: "Le nom est obligatoire.")]
    #[Assert\Length(min: 2, max: 120)]
    private ?string $name = null;

    #[ORM\Column(type: "text", nullable: true)]
    #[Assert\Length(max: 2000)]
    private ?string $description = null;

    #[ORM\Column(type: "datetime_immutable")]
    private ?\DateTimeImmutable $dateCreation = null;

    #[ORM\Column(length: 30, nullable: true)] // Made nullable as it might be moved to parent conceptually, but keeping it here for data safety? 
    // Actually, user said 'Corriger', so if this field should be on Parent, maybe I should remove it? 
    // But I'm keeping 'name' as Title. 
    // 'level' should be on Parent (Cours). 
    // If I keep it here, it's just extra data. 
    // I previously decided to duplicate/move fields. 
    // Use nullable to avoid errors during migration.
    private ?string $level = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    // ✅ New Fields moved from Cours (Child Data)
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $type = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $contenu = null;

    // ✅ New Relation: Module (Many) belongs to One Cours
    #[ORM\ManyToOne(targetEntity: Cours::class, inversedBy: 'modules')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Cours $cours = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $scheduledAt = null;

    public function __construct()
    {
        $this->dateCreation = new \DateTimeImmutable();
        $this->scheduledAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    // ✅ TWIG ALIAS: module.titre -> name
    public function getTitre(): ?string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getDateCreation(): ?\DateTimeImmutable
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeImmutable $dateCreation): self
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    public function getLevel(): ?string
    {
        return $this->level;
    }

    public function setLevel(?string $level): self
    {
        $this->level = $level;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;
        return $this;
    }

    // ✅ New Getters/Setters

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(?string $contenu): self
    {
        $this->contenu = $contenu;
        return $this;
    }

    // ✅ Relation Cours (Parent)
    public function getCours(): ?Cours
    {
        return $this->cours;
    }

    public function setCours(?Cours $cours): self
    {
        $this->cours = $cours;
        return $this;
    }

    public function getScheduledAt(): ?\DateTimeInterface
    {
        return $this->scheduledAt;
    }

    public function setScheduledAt(?\DateTimeInterface $scheduledAt): self
    {
        $this->scheduledAt = $scheduledAt;
        return $this;
    }
}
