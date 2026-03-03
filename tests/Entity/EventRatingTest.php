<?php

namespace App\Tests\Entity;

use App\Entity\EventRating;
use App\Entity\Event;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class EventRatingTest extends TestCase
{
    public function testEventRatingEntity()
    {
        $rating = new EventRating();
        
        $event = $this->createMock(Event::class);
        $rating->setEvent($event);
        $this->assertSame($event, $rating->getEvent());
        
        $user = $this->createMock(User::class);
        $rating->setUser($user);
        $this->assertSame($user, $rating->getUser());
        
        $rating->setScore(5);
        $this->assertEquals(5, $rating->getScore());
    }
}
