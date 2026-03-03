<?php

namespace App\Tests\Entity;

use App\Entity\Module;
use App\Entity\Course;
use App\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\File;

class ModuleTest extends TestCase
{
    public function testModuleEntity()
    {
        $creator = $this->createMock(User::class);
        $module = new Module($creator);
        
        // Test Title
        $module->setTitle('Module 1');
        $this->assertEquals('Module 1', $module->getTitle());
        $this->assertEquals('Module 1', (string) $module);
        
        // Test Description
        $module->setDescription('Module Description');
        $this->assertEquals('Module Description', $module->getDescription());
        
        // Test Level
        $module->setLevel('Intermediate');
        $this->assertEquals('Intermediate', $module->getLevel());
        
        // Test Type
        $module->setType('Video');
        $this->assertEquals('Video', $module->getType());
        
        // Test Content
        $module->setContent('Sample content');
        $this->assertEquals('Sample content', $module->getContent());
        
        // Test ScheduledAt
        $date = new \DateTimeImmutable('2024-01-01');
        $module->setScheduledAt($date);
        $this->assertEquals($date, $module->getScheduledAt());
        
        // Test Course
        $course = $this->createMock(Course::class);
        $module->setCourse($course);
        $this->assertSame($course, $module->getCourse());
        
        // Test Blameable
        $this->assertSame($creator, $module->getCreatedBy());
        
        // Test Timestampable
        $this->assertInstanceOf(\DateTimeImmutable::class, $module->getCreatedAt());
        
        // Test Vich fields
        $file = $this->createMock(File::class);
        $module->setImageFile($file);
        $this->assertSame($file, $module->getImageFile());
        $module->setDocumentFile($file);
        $this->assertSame($file, $module->getDocumentFile());
    }
}
