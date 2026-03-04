<?php

namespace App\Tests\Service;

use App\Entity\Reclamation;
use App\Service\ReclamationManager;
use PHPUnit\Framework\TestCase;

class ReclamationManagerTest extends TestCase
{
    public function testValidationReussie()
    {
        $reclamation = new Reclamation();
        $reclamation->setSujet('Problème de connexion');
        $reclamation->setDescription('Impossible de me connecter à mon profil.');

        $manager = new ReclamationManager();

        $this->assertTrue($manager->validate($reclamation));
    }

    public function testSujetVide()
    {
        $this->expectException(\InvalidArgumentException::class);

        $reclamation = new Reclamation();
        $reclamation->setDescription('Test sans sujet');

        $manager = new ReclamationManager();
        $manager->validate($reclamation);
    }

    public function testDescriptionVide()
    {
        $this->expectException(\InvalidArgumentException::class);

        $reclamation = new Reclamation();
        $reclamation->setSujet('Test sans description');

        $manager = new ReclamationManager();
        $manager->validate($reclamation);
    }
}
