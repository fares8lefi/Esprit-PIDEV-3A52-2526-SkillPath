<?php

namespace App\Tests\Entity;

use App\Entity\Location;
use App\Entity\Event;
use PHPUnit\Framework\TestCase;

class LocationTest extends TestCase
{
    public function testLocationEntity()
    {
        $location = new Location();
        
        $location->setName('Main Hall');
        $this->assertEquals('Main Hall', $location->getName());
        $this->assertEquals('Main Hall', (string) $location);
        
        $location->setBuilding('Building A');
        $this->assertEquals('Building A', $location->getBuilding());
        
        $location->setRoomNumber('101');
        $this->assertEquals('101', $location->getRoomNumber());
        
        $location->setMaxCapacity(100);
        $this->assertEquals(100, $location->getMaxCapacity());
        
        $location->setImage('location.jpg');
        $this->assertEquals('location.jpg', $location->getImage());
        
        // Test Events relation
        $event = $this->createMock(Event::class);
        $location->addEvent($event);
        $this->assertCount(1, $location->getEvents());
        $location->removeEvent($event);
        $this->assertCount(0, $location->getEvents());
    }
}
