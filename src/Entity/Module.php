<?php

namespace App\Entity;

use App\Repository\ModuleRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Course;

use App\Entity\Trait\BlameableTrait;
use App\Entity\Trait\TimestampableTrait;

use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: ModuleRepository::class)]
#[Vich\Uploadable]
class Module
{
    use BlameableTrait;
    use TimestampableTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function __construct(User $createdBy)
    {
        $this->createdBy = $createdBy;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\Column(length: 120)]
    #[Assert\NotBlank(message: "Le titre est obligatoire.")]
    #[Assert\Length(min: 2, max: 120)]
    private string $title;

    #[ORM\Column(type: "text", nullable: true)]
    #[Assert\Length(max: 2000)]
    private ?string $description = null;

    #[ORM\Column(type: "datetime_immutable")]
    #[Gedmo\Timestampable(on: 'create')]
    private \DateTimeImmutable $createdAt;


    #[ORM\Column(length: 30, nullable: true)]
    private ?string $level = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[Vich\UploadableField(mapping: 'module_images', fileNameProperty: 'image')]
    private ?File $imageFile = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $type = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $content = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $document = null;

    #[Vich\UploadableField(mapping: 'module_documents', fileNameProperty: 'document')]
    private ?File $documentFile = null;

    #[ORM\ManyToOne(targetEntity: Course::class, inversedBy: 'modules')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?Course $course = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $scheduledAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
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

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): static
    {
        $this->content = $content;
        return $this;
    }

    public function getCourse(): ?Course
    {
        return $this->course;
    }

    public function setCourse(?Course $course): static
    {
        $this->course = $course;
        return $this;
    }

    public function getDocument(): ?string
    {
        return $this->document;
    }

    public function setDocument(?string $document): static
    {
        $this->document = $document;
        return $this;
    }

    public function getScheduledAt(): ?\DateTimeImmutable
    {
        return $this->scheduledAt;
    }

    public function setScheduledAt(?\DateTimeImmutable $scheduledAt): static
    {
        $this->scheduledAt = $scheduledAt;
        return $this;
    }

    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setDocumentFile(?File $documentFile = null): void
    {
        $this->documentFile = $documentFile;

        if (null !== $documentFile) {
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getDocumentFile(): ?File
    {
        return $this->documentFile;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function __toString(): string
    {
        return $this->title ?? '';
    }
}
