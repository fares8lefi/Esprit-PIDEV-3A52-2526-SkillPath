<?php

namespace App\Service;

use App\Entity\User;

class UserManager
{
    /**
     * Vérifie si l'utilisateur est complètement vérifié.
     */
    public function isVerified(User $user): bool
    {
        return $user->isVerified() && $user->getVerificationCode() === null;
    }

    /**
     * Retourne une chaîne de caractères lisible représentant les rôles.
     */
    public function getRolesForDisplay(User $user): string
    {
        $roles = $user->getRoles();
        if (in_array('ROLE_ADMIN', $roles)) {
            return 'Administrateur';
        }
        return 'Utilisateur Standard';
    }

    /**
     * Vérifie si l'utilisateur a complété son profil IA.
     */
    public function hasCompletedAiProfile(User $user): bool
    {
        return !empty($user->getDomaine()) && !empty($user->getStyleDapprentissage()) && !empty($user->getNiveau());
    }
}
