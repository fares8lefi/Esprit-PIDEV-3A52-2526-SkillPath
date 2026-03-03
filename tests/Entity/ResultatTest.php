<?php

namespace App\Tests\Entity;

use App\Entity\Resultat;
use App\Entity\Quiz;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class ResultatTest extends TestCase
{
    public function testResultatEntity()
    {
        $resultat = new Resultat();
        
        $quiz = $this->createMock(Quiz::class);
        $resultat->setQuiz($quiz);
        $this->assertSame($quiz, $resultat->getQuiz());
        
        $etudiant = $this->createMock(User::class);
        $resultat->setEtudiant($etudiant);
        $this->assertSame($etudiant, $resultat->getEtudiant());
        
        $resultat->setScore(15);
        $this->assertEquals(15, $resultat->getScore());
        
        $resultat->setNoteMax(20);
        $this->assertEquals(20, $resultat->getNoteMax());
        
        $this->assertInstanceOf(\DateTimeInterface::class, $resultat->getDatePassage());
        $date = new \DateTimeImmutable('2024-02-01');
        $resultat->setDatePassage($date);
        $this->assertEquals($date, $resultat->getDatePassage());
    }
}
