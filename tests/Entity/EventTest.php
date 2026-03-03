<?php

namespace App\Tests\Entity;

use App\Entity\Event;
use App\Entity\Location;
use App\Entity\User;
use App\Entity\EventRating;
use PHPUnit\Framework\TestCase;

class EventTest extends TestCase
{
    public function testEventEntity()
    {
        $event = new Event();
        
        // Test Title
        $event->setTitle('SkillPath Conference');
        $this->assertEquals('SkillPath Conference', $event->getTitle());
        
        // Test Description
        $event->setDescription('A great conference');
        $this->assertEquals('A great conference', $event->getDescription());
        
        // Test Dates
        $date = new \DateTime('2024-05-01');
        $event->setEventDate($date);
        $this->assertEquals($date, $event->getEventDate());
        
        $start = new \DateTime('10:00:00');
        $event->setStartTime($start);
        $this->assertEquals($start, $event->getStartTime());
        
        $end = new \DateTime('18:00:00');
        $event->setEndTime($end);
        $this->assertEquals($end, $event->getEndTime());
        
        // Test Location
        $location = $this->createMock(Location::class);
        $event->setLocation($location);
        $this->assertSame($location, $event->getLocation());
        
        // Test Ratings
        $rating1 = $this->createMock(EventRating::class);
        $rating1->method('getScore')->willReturn(5);
        $rating2 = $this->createMock(EventRating::class);
        $rating2->method('getScore')->willReturn(3);
        
        $event->addRating($rating1);
        $event->addRating($rating2);
        
        $this->assertCount(2, $event->getRatings());
        $this->assertEquals(4.0, $event->getAverageRating());
        
        $event->removeRating($rating1);
        $this->assertCount(1, $event->getRatings());
        $this->assertEquals(3.0, $event->getAverageRating());
        
        // Test Participants
        $user = $this->createMock(User::class);
        $event->addParticipant($user);
        $this->assertCount(1, $event->getParticipants());
        $event->removeParticipant($user);
        $this->assertCount(0, $event->getParticipants());
    }
}
