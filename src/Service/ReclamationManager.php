<?php
namespace App\Service;

use App\Entity\Reclamation;

class ReclamationManager
{
    public function validate(Reclamation $reclamation): bool
    {
        if (empty($reclamation->getSujet())) {
            throw new \InvalidArgumentException('Le sujet est obligatoire');
        }
        
        if (empty($reclamation->getDescription())) {
            throw new \InvalidArgumentException('La description est obligatoire');
        }
        
        return true;
    }
}
