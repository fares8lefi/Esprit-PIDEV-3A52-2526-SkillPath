<?php

namespace App\Tests\Entity;

use App\Entity\Course;
use App\Entity\Module;
use App\Entity\Quiz;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class CourseTest extends TestCase
{
    public function testCourseEntity()
    {
        $creator = $this->createMock(User::class);
        $course = new Course($creator);
        
        // Test Title
        $course->setTitle('Symfony Course');
        $this->assertEquals('Symfony Course', $course->getTitle());
        $this->assertEquals('Symfony Course', (string) $course);
        
        // Test Description
        $course->setDescription('Learn Symfony');
        $this->assertEquals('Learn Symfony', $course->getDescription());
        
        // Test Level
        $course->setLevel('Advanced');
        $this->assertEquals('Advanced', $course->getLevel());
        
        // Test Category
        $course->setCategory('Web Development');
        $this->assertEquals('Web Development', $course->getCategory());
        
        // Test Duration
        $course->setDuration(120);
        $this->assertEquals(120, $course->getDuration());
        
        // Test Price
        $course->setPrice('99.99');
        $this->assertEquals('99.99', $course->getPrice());
        
        // Test Rating
        $course->setRating(4.5);
        $this->assertEquals(4.5, $course->getRating());
        
        // Test Blameable (Constructor sets creator)
        $this->assertSame($creator, $course->getCreatedBy());
        
        // Test Timestampable
        $this->assertInstanceOf(\DateTimeImmutable::class, $course->getCreatedAt());
        
        // Test Collections
        $module = $this->createMock(Module::class);
        $module->method('getCourse')->willReturn($course); // Handle internal setter logic if any
        $course->addModule($module);
        $this->assertCount(1, $course->getModules());
        $course->removeModule($module);
        $this->assertCount(0, $course->getModules());
        
        $quiz = $this->createMock(Quiz::class);
        $quiz->method('getCourse')->willReturn($course);
        $course->addQuiz($quiz);
        $this->assertCount(1, $course->getQuizzes());
        $course->removeQuiz($quiz);
        $this->assertCount(0, $course->getQuizzes());
        
        $user = $this->createMock(User::class);
        $course->addUser($user);
        $this->assertCount(1, $course->getUsers());
        $course->removeUser($user);
        $this->assertCount(0, $course->getUsers());
    }
}
