<?php

namespace App\Tests\Entity;

use App\Entity\Certificate;
use App\Entity\User;
use App\Entity\Course;
use PHPUnit\Framework\TestCase;

class CertificateTest extends TestCase
{
    public function testCertificateEntity(): void
    {
        $certificate = new Certificate();
        
        $certificate->setCertCode('CERT-123-ABC');
        $this->assertEquals('CERT-123-ABC', $certificate->getCertCode());
        
        $user = $this->createMock(User::class);
        $certificate->setUser($user);
        $this->assertSame($user, $certificate->getUser());
        
        $course = $this->createMock(Course::class);
        $certificate->setCourse($course);
        $this->assertSame($course, $certificate->getCourse());
        
        $this->assertInstanceOf(\DateTimeInterface::class, $certificate->getIssuedAt());
    }
}
