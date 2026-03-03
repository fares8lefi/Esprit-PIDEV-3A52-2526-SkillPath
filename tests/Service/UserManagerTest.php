<?php

namespace App\Tests\Service;

use App\Entity\User;
use App\Service\UserManager;
use PHPUnit\Framework\TestCase;

class UserManagerTest extends TestCase
{
    private UserManager $userManager;

    protected function setUp(): void
    {
        $this->userManager = new UserManager();
    }

    public function testIsVerified(): void
    {
        $user = new User();
        
        // Non vérifié au départ
        $this->assertFalse($this->userManager->isVerified($user));

        // Avec code de vérification -> Pas encore validé complètement
        $user->setIsVerified(true);
        $user->setVerificationCode('123456');
        $this->assertFalse($this->userManager->isVerified($user));

        // Vérifié et sans code -> Valide
        $user->setVerificationCode(null);
        $this->assertTrue($this->userManager->isVerified($user));
    }

    public function testGetRolesForDisplay(): void
    {
        $user = new User();
        
        // Rôle utilisateur standard
        $user->setRole('user');
        $this->assertEquals('Utilisateur Standard', $this->userManager->getRolesForDisplay($user));

        // Rôle administrateur
        $user->setRole('admin');
        $this->assertEquals('Administrateur', $this->userManager->getRolesForDisplay($user));
    }

    public function testHasCompletedAiProfile(): void
    {
        $user = new User();
        
        $this->assertFalse($this->userManager->hasCompletedAiProfile($user));

        $user->setDomaine('Dev');
        $user->setStyleDapprentissage('visuel');
        $user->setNiveau('Débutant');

        $this->assertTrue($this->userManager->hasCompletedAiProfile($user));
    }
}
