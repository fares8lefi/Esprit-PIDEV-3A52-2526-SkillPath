<?php

namespace App\Service;

use App\Entity\Quiz;

class QuizManager
{
    public function validate(Quiz $quiz): bool
    {
        if (empty($quiz->getTitle())) {
            throw new \InvalidArgumentException('Le titre du quiz est obligatoire');
        }

        if ($quiz->getDuration() <= 0) {
            throw new \InvalidArgumentException('La durée doit être supérieure à 0');
        }

        return true;
    }
}