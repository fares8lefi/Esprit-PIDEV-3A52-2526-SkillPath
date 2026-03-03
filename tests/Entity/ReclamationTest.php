<?php

namespace App\Tests\Entity;

use App\Entity\Reclamation;
use App\Entity\Reponse;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class ReclamationTest extends TestCase
{
    public function testReclamationEntity()
    {
        $reclamation = new Reclamation();
        
        $reclamation->setSujet('Issue with login');
        $this->assertEquals('Issue with login', $reclamation->getSujet());
        
        $reclamation->setDescription('Cannot login with Google');
        $this->assertEquals('Cannot login with Google', $reclamation->getDescription());
        
        $this->assertEquals('En attente', $reclamation->getStatut());
        $reclamation->setStatut('Solved');
        $this->assertEquals('Solved', $reclamation->getStatut());
        
        $reclamation->setPieceJointe('error.png');
        $this->assertEquals('error.png', $reclamation->getPieceJointe());
        
        $user = $this->createMock(User::class);
        $reclamation->setUser($user);
        $this->assertSame($user, $reclamation->getUser());
        
        // Test Reponses relation
        $reponse = $this->createMock(Reponse::class);
        $reclamation->addReponse($reponse);
        $this->assertCount(1, $reclamation->getReponses());
        $reclamation->removeReponse($reponse);
        $this->assertCount(0, $reclamation->getReponses());
    }
}
