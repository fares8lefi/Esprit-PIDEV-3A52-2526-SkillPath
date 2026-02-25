<?php

namespace App\Entity;

use App\Repository\CoursRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\Module;

#[ORM\Entity(repositoryClass: CoursRepository::class)]
#[ORM\Table(name: 'cours')]
class Cours
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le titre est obligatoire')]
    private ?string $titre = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $level = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $categorie = null;

    // ✅ New Relation: Cours (One) has Many Modules
    #[ORM\OneToMany(mappedBy: 'cours', targetEntity: Module::class, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['scheduledAt' => 'ASC'])]
    private Collection $modules;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'cours')]
    private Collection $users;

    // ❌ Fields moved to Module (kept as nullable/unused or removed? User said 'Corriger', implying move. We will remove them from here if they belong to child now, BUT for safety on existing data we might want to keep them for a second? No, user wants correct architecture. I will remove strict constraints on old fields if I keep them, but better to replace them.)
    // Wait, I can't easily iterate schema if I delete columns. I will Add new columns.
    // I will REMOVE 'type' and 'contenu' from here as they belong to Module?
    // Actually, I'll keep 'titre' as the Name.
    // 'type' and 'contenu' -> Move to Module.
    
    // ... Keeping 'titre' ...

    public function getId(): ?int
    {
        return $this->id;
    }

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->modules = new ArrayCollection();
        $this->users = new ArrayCollection();
    }
    
    // ... Getters/Setters ...

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;
        return $this;
    }

    // ✅ TWIG ALIAS: cours.name -> titre
    public function getName(): ?string
    {
        return $this->titre;
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

    public function getLevel(): ?string
    {
        return $this->level;
    }

    public function setLevel(?string $level): static
    {
        $this->level = $level;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;
        return $this;
    }

    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    public function setCategorie(?string $categorie): static
    {
        $this->categorie = $categorie;
        return $this;
    }

    /**
     * @return Collection<int, Module>
     */
    public function getModules(): Collection
    {
        return $this->modules;
    }

    public function addModule(Module $module): static
    {
        if (!$this->modules->contains($module)) {
            $this->modules->add($module);
            $module->setCours($this);
        }
        return $this;
    }

    public function removeModule(Module $module): static
    {
        if ($this->modules->removeElement($module)) {
            // set the owning side to null (unless already changed)
            if ($module->getCours() === $this) {
                $module->setCours(null);
            }
        }
        return $this;
    }

    // ✅ TWIG ALIAS: cours.cours -> modules
    public function getCours(): Collection
    {
        return $this->modules;
    }

    // ✅ TWIG ALIAS: cours.dateCreation -> createdAt
    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }


    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->addCour($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            $user->removeCour($this);
        }

        return $this;
    }
}