<?php

namespace App\Entity;

use App\Repository\ResultatRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: ResultatRepository::class)]
class Resultat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_resultat')]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'resultats')]
    #[ORM\JoinColumn(name: 'quiz_id', referencedColumnName: 'id_quiz', nullable: false, onDelete: 'CASCADE')]
    private Quiz $quiz;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'etudiant_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private User $etudiant;

    #[ORM\Column]
    private int $score;

    #[ORM\Column(name: 'note_max')]
    private int $noteMax;

    #[ORM\Column(name: 'date_passage', type: Types::DATETIME_MUTABLE)]
    #[Gedmo\Timestampable(on: 'create')]
    private \DateTimeInterface $datePassage;

    public function __construct()
    {
        $this->datePassage = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuiz(): Quiz
    {
        return $this->quiz;
    }

    public function setQuiz(Quiz $quiz): static
    {
        $this->quiz = $quiz;
        return $this;
    }

    public function getEtudiant(): User
    {
        return $this->etudiant;
    }

    public function setEtudiant(User $etudiant): static
    {
        $this->etudiant = $etudiant;
        return $this;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(int $score): static
    {
        $this->score = $score;

        return $this;
    }

    public function getNoteMax(): ?int
    {
        return $this->noteMax;
    }

    public function setNoteMax(int $noteMax): static
    {
        $this->noteMax = $noteMax;

        return $this;
    }

    public function getDatePassage(): \DateTimeInterface
    {
        return $this->datePassage;
    }
}
