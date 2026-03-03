<?php

namespace App\Tests\Entity;

use App\Entity\Question;
use App\Entity\Quiz;
use PHPUnit\Framework\TestCase;

class QuestionTest extends TestCase
{
    public function testQuestionEntity()
    {
        $question = new Question();
        
        $quiz = $this->createMock(Quiz::class);
        $question->setQuiz($quiz);
        $this->assertSame($quiz, $question->getQuiz());
        
        $question->setEnonce('What is Symfony?');
        $this->assertEquals('What is Symfony?', $question->getEnonce());
        
        $question->setChoixA('A framework');
        $this->assertEquals('A framework', $question->getChoixA());
        $question->setChoixB('A language');
        $this->assertEquals('A language', $question->getChoixB());
        $question->setChoixC('A library');
        $this->assertEquals('A library', $question->getChoixC());
        $question->setChoixD('None');
        $this->assertEquals('None', $question->getChoixD());
        
        $question->setBonneReponse('A');
        $this->assertEquals('A', $question->getBonneReponse());
        
        $question->setPoints(5);
        $this->assertEquals(5, $question->getPoints());
    }
}
