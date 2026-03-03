<?php

namespace App\Entity\Embeddable;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Embeddable]
class Email
{
    #[ORM\Column(name: 'email', length: 255)]
    #[Assert\NotBlank(message: "L'adresse email est obligatoire.")]
    #[Assert\Email(message: "Veuillez entrer une adresse email valide.")]
    private string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
