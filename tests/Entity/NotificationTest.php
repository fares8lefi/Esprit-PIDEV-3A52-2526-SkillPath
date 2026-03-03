<?php

namespace App\Tests\Entity;

use App\Entity\Notification;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class NotificationTest extends TestCase
{
    public function testNotificationEntity()
    {
        $creator = $this->createMock(User::class);
        $notification = new Notification($creator);
        
        $user = $this->createMock(User::class);
        $notification->setUser($user);
        $this->assertSame($user, $notification->getUser());
        
        $notification->setTitle('Welcome');
        $this->assertEquals('Welcome', $notification->getTitle());
        
        $notification->setMessage('Hello User');
        $this->assertEquals('Hello User', $notification->getMessage());
        
        $notification->setLink('/home');
        $this->assertEquals('/home', $notification->getLink());
        
        $this->assertFalse($notification->isRead());
        $notification->setIsRead(true);
        $this->assertTrue($notification->isRead());
        
        $this->assertSame($creator, $notification->getCreatedBy());
        $this->assertInstanceOf(\DateTimeImmutable::class, $notification->getCreatedAt());
    }
}
