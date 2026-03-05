<?php

namespace App\Tests\Service;

use App\Entity\Reclamation;
use App\Service\ReclamationManager;
use PHPUnit\Framework\TestCase;

class ReclamationManagerTest extends TestCase
{
    public function testValidationReussie(): void
    {
        $reclamation = new Reclamation();
        $reclamation->setSujet('Problème de connexion');
        $reclamation->setDescription('Impossible de me connecter à mon profil.');

        $manager = new ReclamationManager();

        $this->assertTrue($manager->validate($reclamation));
    }

    public function testSujetVide(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $reclamation = new Reclamation();
        $reclamation->setSujet('');
        $reclamation->setDescription('Test sans sujet');

        $manager = new ReclamationManager();
        $manager->validate($reclamation);
    }

    public function testDescriptionVide(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $reclamation = new Reclamation();
        $reclamation->setSujet('Test sans description');
        $reclamation->setDescription('');

        $manager = new ReclamationManager();
        $manager->validate($reclamation);
    }
}
