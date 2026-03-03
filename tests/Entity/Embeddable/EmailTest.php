<?php

namespace App\Tests\Entity\Embeddable;

use App\Entity\Embeddable\Email;
use PHPUnit\Framework\TestCase;

class EmailTest extends TestCase
{
    public function testEmailEmbeddable()
    {
        $value = 'test@example.com';
        $email = new Email($value);
        
        $this->assertEquals($value, $email->getValue());
        $this->assertEquals($value, (string) $email);
    }
}
