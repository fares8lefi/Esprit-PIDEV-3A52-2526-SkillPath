<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: EventRepository::class)]
#[Vich\Uploadable]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $title;

    #[ORM\Column(length: 255)]
    private string $description;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private \DateTime $eventDate;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private \DateTime $startTime;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private \DateTime $endTime;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[Vich\UploadableField(mapping: 'event_images', fileNameProperty: 'image')]
    private ?File $imageFile = null;



    #[ORM\ManyToOne(inversedBy: 'events')]
    private ?Location $location = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'joinedEvents')]
    private Collection $participants;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'favoriteEvents')]
    private Collection $favoritedBy;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $averageRating = null;

    /**
     * @var Collection<int, EventRating>
     */
    #[ORM\OneToMany(mappedBy: 'event', targetEntity: EventRating::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $ratings;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
        $this->favoritedBy = new ArrayCollection();
        $this->ratings = new ArrayCollection();
    }

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

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getEventDate(): ?\DateTime
    {
        return $this->eventDate;
    }

    public function setEventDate(\DateTime $eventDate): static
    {
        $this->eventDate = $eventDate;

        return $this;
    }

    public function getStartTime(): ?\DateTime
    {
        return $this->startTime;
    }

    public function setStartTime(\DateTime $startTime): static
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getEndTime(): ?\DateTime
    {
        return $this->endTime;
    }

    public function setEndTime(\DateTime $endTime): static
    {
        $this->endTime = $endTime;

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

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile|null $imageFile
     */
    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;


    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): static
    {
        $this->location = $location;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(User $participant): static
    {
        if (!$this->participants->contains($participant)) {
            $this->participants->add($participant);
            $participant->addJoinedEvent($this);
        }

        return $this;
    }

    public function removeParticipant(User $participant): static
    {
        if ($this->participants->removeElement($participant)) {
            $participant->removeJoinedEvent($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getFavoritedBy(): Collection
    {
        return $this->favoritedBy;
    }

    public function addFavoritedBy(User $favoritedBy): static
    {
        if (!$this->favoritedBy->contains($favoritedBy)) {
            $this->favoritedBy->add($favoritedBy);
            $favoritedBy->addFavoriteEvent($this);
        }

        return $this;
    }

    public function removeFavoritedBy(User $favoritedBy): static
    {
        if ($this->favoritedBy->removeElement($favoritedBy)) {
            $favoritedBy->removeFavoriteEvent($this);
        }

        return $this;
    }

    public function getAverageRating(): ?float
    {
        return $this->averageRating;
    }

    public function setAverageRating(?float $averageRating): static
    {
        $this->averageRating = $averageRating;

        return $this;
    }

    /**
     * @return Collection<int, EventRating>
     */
    public function getRatings(): Collection
    {
        return $this->ratings;
    }

    public function addRating(EventRating $rating): static
    {
        if (!$this->ratings->contains($rating)) {
            $this->ratings->add($rating);
            $rating->setEvent($this);
            $this->updateAverageRating();
        }

        return $this;
    }

    public function removeRating(EventRating $rating): static
    {
        if ($this->ratings->removeElement($rating)) {
            $this->updateAverageRating();
        }

        return $this;
    }

    public function updateAverageRating(): void
    {
        if ($this->ratings->isEmpty()) {
            $this->averageRating = null;
            return;
        }

        $sum = 0;
        foreach ($this->ratings as $rating) {
            $sum += $rating->getScore();
        }

        $this->averageRating = round($sum / $this->ratings->count(), 1);
    }
}
