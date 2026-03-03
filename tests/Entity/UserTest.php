<?php

namespace App\Tests\Entity;

use App\Entity\User;
use App\Entity\Course;
use App\Entity\Event;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\UuidV7;

class UserTest extends TestCase
{
    public function testUserEntity()
    {
        $user = new User();
        
        // Test ID initialization
        $this->assertInstanceOf(UuidV7::class, $user->getId());
        
        // Test Email
        $email = 'test@example.com';
        $user->setEmail($email);
        $this->assertEquals($email, $user->getEmail());
        $this->assertEquals($email, $user->getUserIdentifier());
        
        // Test Username
        $username = 'testuser';
        $user->setUsername($username);
        $this->assertEquals($username, $user->getUsername());
        
        // Test Status
        $status = 'active';
        $user->setStatus($status);
        $this->assertEquals($status, $user->getStatus());
        
        // Test Role
        $role = 'student';
        $user->setRole($role);
        $this->assertEquals($role, $user->getRole());
        $this->assertContains('ROLE_USER', $user->getRoles());
        
        // Test Admin Role
        $user->setRole('admin');
        $this->assertContains('ROLE_ADMIN', $user->getRoles());
        
        // Test Password
        $password = 'hashed_password';
        $user->setPassword($password);
        $this->assertEquals($password, $user->getPassword());
        
        // Test isVerified
        $user->setIsVerified(true);
        $this->assertTrue($user->isVerified());
        
        // Test Verification Code
        $code = '123456';
        $user->setVerificationCode($code);
        $this->assertEquals($code, $user->getVerificationCode());
        
        // Test Domaine, Style, Niveau
        $user->setDomaine('IT');
        $this->assertEquals('IT', $user->getDomaine());
        $user->setStyleDapprentissage('Visual');
        $this->assertEquals('Visual', $user->getStyleDapprentissage());
        $user->setNiveau('Beginner');
        $this->assertEquals('Beginner', $user->getNiveau());
        
        // Test Collections
        $course = $this->createMock(Course::class);
        $user->addCourse($course);
        $this->assertCount(1, $user->getCourses());
        $user->removeCourse($course);
        $this->assertCount(0, $user->getCourses());
        
        $event = $this->createMock(Event::class);
        $user->addJoinedEvent($event);
        $this->assertCount(1, $user->getJoinedEvents());
        $user->removeJoinedEvent($event);
        $this->assertCount(0, $user->getJoinedEvents());
        
        $user->addFavoriteEvent($event);
        $this->assertCount(1, $user->getFavoriteEvents());
        $user->removeFavoriteEvent($event);
        $this->assertCount(0, $user->getFavoriteEvents());
    }
}
