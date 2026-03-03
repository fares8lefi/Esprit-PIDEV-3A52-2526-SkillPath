<?php

namespace App\Tests\Entity;

use App\Entity\Reponse;
use App\Entity\Reclamation;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class ReponseTest extends TestCase
{
    public function testReponseEntity()
    {
        $reponse = new Reponse();
        
        $reponse->setMessage('System is down');
        $this->assertEquals('System is down', $reponse->getMessage());
        
        $reclamation = $this->createMock(Reclamation::class);
        $reponse->setReclamation($reclamation);
        $this->assertSame($reclamation, $reponse->getReclamation());
        
        $user = $this->createMock(User::class);
        $reponse->setUser($user);
        $this->assertSame($user, $reponse->getUser());
    }
}
