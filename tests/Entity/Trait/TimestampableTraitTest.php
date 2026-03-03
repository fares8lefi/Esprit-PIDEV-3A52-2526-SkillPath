<?php

namespace App\Tests\Entity\Trait;

use App\Entity\Trait\TimestampableTrait;
use PHPUnit\Framework\TestCase;

class TimestampableTraitTest extends TestCase
{
    private $traitObject;

    protected function setUp(): void
    {
        $this->traitObject = new class {
            use TimestampableTrait;
        };
    }

    public function testTimestampableTrait()
    {
        $date = new \DateTimeImmutable();
        
        $this->traitObject->setUpdatedAt($date);
        $this->assertSame($date, $this->traitObject->getUpdatedAt());
        
        $this->traitObject->setUpdatedAt(null);
        $this->assertNull($this->traitObject->getUpdatedAt());
    }
}
