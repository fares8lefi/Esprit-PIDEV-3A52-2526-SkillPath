<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if (!$user->isVerified()) {
            
            throw new CustomUserMessageAuthenticationException('Votre compte n\'est pas encore vérifié. Veuillez vérifier votre boîte mail pour le code de validation.');
        }

        if ($user->getStatus() !== 'active') {
            
            throw new CustomUserMessageAuthenticationException('Votre compte est actuellement inactif. Veuillez contacter l\'administration.');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
       
    }
}
