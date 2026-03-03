<?php

namespace App\Tests\Entity;

use App\Entity\UserCourseView;
use App\Entity\User;
use App\Entity\Course;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\UuidV7;

class UserCourseViewTest extends TestCase
{
    public function testUserCourseViewEntity()
    {
        $view = new UserCourseView();
        
        $this->assertInstanceOf(UuidV7::class, $view->getId());
        
        $user = $this->createMock(User::class);
        $view->setUser($user);
        $this->assertSame($user, $view->getUser());
        
        $course = $this->createMock(Course::class);
        $view->setCourse($course);
        $this->assertSame($course, $view->getCourse());
        
        $view->setTimeSpent(3600);
        $this->assertEquals(3600, $view->getTimeSpent());
        
        $view->setQuizScore(85.5);
        $this->assertEquals(85.5, $view->getQuizScore());
        
        $view->setEngagementLevel('high');
        $this->assertEquals('high', $view->getEngagementLevel());
        
        $view->setIsEnrolled(true);
        $this->assertTrue($view->isEnrolled());
        
        $view->setIsCompleted(true);
        $this->assertTrue($view->isCompleted());
        
        $view->setMaxModuleReached(5);
        $this->assertEquals(5, $view->getMaxModuleReached());
    }
}
