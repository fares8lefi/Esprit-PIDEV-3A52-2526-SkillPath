<?php

namespace App\Tests\Entity\Trait;

use App\Entity\Trait\BlameableTrait;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class BlameableTraitTest extends TestCase
{
    private $traitObject;

    protected function setUp(): void
    {
        // Create an anonymous class using the trait for testing
        $this->traitObject = new class {
            use BlameableTrait;
        };
    }

    public function testBlameableTrait()
    {
        $user = $this->createMock(User::class);
        
        $this->traitObject->setCreatedBy($user);
        $this->assertSame($user, $this->traitObject->getCreatedBy());
        
        $this->traitObject->setUpdatedBy($user);
        $this->assertSame($user, $this->traitObject->getUpdatedBy());
        
        $this->traitObject->setUpdatedBy(null);
        $this->assertNull($this->traitObject->getUpdatedBy());
    }
}
