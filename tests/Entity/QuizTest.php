<?php

namespace App\Tests\Entity;

use App\Entity\Quiz;
use App\Entity\Question;
use App\Entity\Resultat;
use App\Entity\Course;
use PHPUnit\Framework\TestCase;

class QuizTest extends TestCase
{
    public function testQuizEntity()
    {
        $quiz = new Quiz();
        
        $quiz->setTitle('Symfony Quiz');
        $this->assertEquals('Symfony Quiz', $quiz->getTitle());
        $this->assertEquals('Symfony Quiz', (string) $quiz);
        
        $quiz->setDescription('A quiz about Symfony');
        $this->assertEquals('A quiz about Symfony', $quiz->getDescription());
        
        $quiz->setDuration(30);
        $this->assertEquals(30, $quiz->getDuration());
        
        $quiz->setNoteMax(20);
        $this->assertEquals(20, $quiz->getNoteMax());
        
        $this->assertInstanceOf(\DateTimeInterface::class, $quiz->getDateCreation());
        
        $course = $this->createMock(Course::class);
        $quiz->setCourse($course);
        $this->assertSame($course, $quiz->getCourse());
        
        // Test Relations
        $question = $this->createMock(Question::class);
        $quiz->addQuestion($question);
        $this->assertCount(1, $quiz->getQuestions());
        $quiz->removeQuestion($question);
        $this->assertCount(0, $quiz->getQuestions());
        
        $resultat = $this->createMock(Resultat::class);
        $quiz->addResultat($resultat);
        $this->assertCount(1, $quiz->getResultats());
        $quiz->removeResultat($resultat);
        $this->assertCount(0, $quiz->getResultats());
    }
}
