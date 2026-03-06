<?php

namespace App\Entity;

use App\Repository\UserCourseViewRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\UuidV7;

#[ORM\Entity(repositoryClass: UserCourseViewRepository::class)]
#[ORM\Table(name: 'user_course_view')]
#[ORM\UniqueConstraint(name: 'user_course_unique', columns: ['user_id', 'course_id'])]
class UserCourseView
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private UuidV7 $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Course::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Course $course = null;

    #[ORM\Column]
    private int $timeSpent = 0;

    #[ORM\Column(nullable: true)]
    private ?float $quizScore = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $engagementLevel = null; // low, medium, high

    #[ORM\Column]
    private bool $isEnrolled = false;

    #[ORM\Column]
    private bool $isCompleted = false;

    #[ORM\Column(options: ["default" => 0])]
    private int $maxModuleReached = 0;

    public function __construct()
    {
        $this->id = new UuidV7();
    }

    public function getId(): UuidV7
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

    public function getCourse(): ?Course
    {
        return $this->course;
    }

    public function setCourse(?Course $course): static
    {
        $this->course = $course;
        return $this;
    }

    public function getTimeSpent(): ?int
    {
        return $this->timeSpent;
    }

    public function setTimeSpent(int $timeSpent): static
    {
        $this->timeSpent = $timeSpent;
        return $this;
    }

    public function getQuizScore(): ?float
    {
        return $this->quizScore;
    }

    public function setQuizScore(?float $quizScore): static
    {
        $this->quizScore = $quizScore;
        return $this;
    }

    public function getEngagementLevel(): ?string
    {
        return $this->engagementLevel;
    }

    public function setEngagementLevel(?string $engagementLevel): static
    {
        $this->engagementLevel = $engagementLevel;
        return $this;
    }

    public function isEnrolled(): ?bool
    {
        return $this->isEnrolled;
    }

    public function setIsEnrolled(bool $isEnrolled): static
    {
        $this->isEnrolled = $isEnrolled;
        return $this;
    }

    public function isCompleted(): ?bool
    {
        return $this->isCompleted;
    }

    public function setIsCompleted(bool $isCompleted): static
    {
        $this->isCompleted = $isCompleted;
        return $this;
    }

    public function getMaxModuleReached(): ?int
    {
        return $this->maxModuleReached;
    }

    public function setMaxModuleReached(int $maxModuleReached): static
    {
        $this->maxModuleReached = $maxModuleReached;
        return $this;
    }
}
