<?php

namespace App\Entity;

use App\Repository\UserCourseViewRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserCourseViewRepository::class)]
#[ORM\Table(name: 'user_course_view')]
class UserCourseView
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Course::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Course $course = null;

    #[ORM\Column]
    private ?int $timeSpent = 0;

    #[ORM\Column(nullable: true)]
    private ?float $quizScore = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $engagementLevel = null; // low, medium, high

    #[ORM\Column]
    private ?bool $isEnrolled = false;

    #[ORM\Column]
    private ?bool $isCompleted = false;

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
}
