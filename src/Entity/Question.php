<?php

namespace App\Entity;

use App\Repository\QuestionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: QuestionRepository::class)]
class Question
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_question')]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'questions')]
    #[ORM\JoinColumn(name: 'quiz_id', referencedColumnName: 'id_quiz', nullable: false, onDelete: 'CASCADE')]
    private ?Quiz $quiz = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "L'énoncé de la question est obligatoire")]
    #[Assert\Length(min: 5, minMessage: "L'énoncé doit faire au moins 5 caractères")]
    private string $enonce;

    #[ORM\Column(name: 'choix_a', length: 255)]
    #[Assert\NotBlank(message: "Le choix A est obligatoire")]
    private string $choixA;

    #[ORM\Column(name: 'choix_b', length: 255)]
    #[Assert\NotBlank(message: "Le choix B est obligatoire")]
    private string $choixB;

    #[ORM\Column(name: 'choix_c', length: 255, nullable: true)]
    private ?string $choixC = null;

    #[ORM\Column(name: 'choix_d', length: 255, nullable: true)]
    private ?string $choixD = null;

    #[ORM\Column(name: 'bonne_reponse', length: 1)]
    #[Assert\NotBlank(message: "La bonne réponse est obligatoire")]
    #[Assert\Choice(choices: ['A', 'B', 'C', 'D'], message: "La bonne réponse doit être A, B, C ou D")]
    private string $bonneReponse;

    #[ORM\Column]
    #[Assert\NotNull(message: "Le nombre de points est obligatoire")]
    #[Assert\Positive(message: "Le nombre de points doit être positif")]
    private int $points;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuiz(): ?Quiz
    {
        return $this->quiz;
    }

    public function setQuiz(Quiz $quiz): static
    {
        $this->quiz = $quiz;
        return $this;
    }

    public function getEnonce(): ?string
    {
        return $this->enonce;
    }

    public function setEnonce(string $enonce): static
    {
        $this->enonce = $enonce;

        return $this;
    }

    public function getChoixA(): ?string
    {
        return $this->choixA;
    }

    public function setChoixA(string $choixA): static
    {
        $this->choixA = $choixA;

        return $this;
    }

    public function getChoixB(): ?string
    {
        return $this->choixB;
    }

    public function setChoixB(string $choixB): static
    {
        $this->choixB = $choixB;

        return $this;
    }

    public function getChoixC(): ?string
    {
        return $this->choixC;
    }

    public function setChoixC(?string $choixC): static
    {
        $this->choixC = $choixC;

        return $this;
    }

    public function getChoixD(): ?string
    {
        return $this->choixD;
    }

    public function setChoixD(?string $choixD): static
    {
        $this->choixD = $choixD;

        return $this;
    }

    public function getBonneReponse(): ?string
    {
        return $this->bonneReponse;
    }

    public function setBonneReponse(string $bonneReponse): static
    {
        $this->bonneReponse = $bonneReponse;

        return $this;
    }

    public function getPoints(): ?int
    {
        return $this->points;
    }

    public function setPoints(int $points): static
    {
        $this->points = $points;

        return $this;
    }
}
