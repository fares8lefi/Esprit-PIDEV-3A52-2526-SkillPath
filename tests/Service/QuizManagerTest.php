<?php

namespace App\Tests\Service;

use App\Entity\Quiz;
use App\Service\QuizManager;
use PHPUnit\Framework\TestCase;

class QuizManagerTest extends TestCase
{
    public function testValidQuiz()
    {
        $quiz = new Quiz();
        $quiz->setTitle('Quiz Symfony');
        $quiz->setDuration(30);

        $manager = new QuizManager();

        $this->assertTrue($manager->validate($quiz));
    }

    public function testQuizWithoutTitle()
    {
        $this->expectException(\InvalidArgumentException::class);

        $quiz = new Quiz();
        $quiz->setDuration(20);

        $manager = new QuizManager();
        $manager->validate($quiz);
    }

    public function testQuizWithInvalidDuration()
    {
        $this->expectException(\InvalidArgumentException::class);

        $quiz = new Quiz();
        $quiz->setTitle('Test');
        $quiz->setDuration(0);

        $manager = new QuizManager();
        $manager->validate($quiz);
    }
}