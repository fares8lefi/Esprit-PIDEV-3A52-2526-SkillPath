<?php

namespace App\Tests\Service;

use App\Entity\Certificate;
use App\Entity\Course;
use App\Entity\User;
use App\Service\CertificateService;
use Nucleos\DompdfBundle\Wrapper\DompdfWrapperInterface;
use PHPUnit\Framework\TestCase;
use Twig\Environment;

class CertificateServiceTest extends TestCase
{
    private $twig;
    private $pdf;
    private $certificateService;

    protected function setUp(): void
    {
        $this->twig = $this->createMock(Environment::class);
        $this->pdf = $this->createMock(DompdfWrapperInterface::class);
        $this->certificateService = new CertificateService($this->twig, $this->pdf);
    }

    public function testGeneratePdfContent(): void
    {
        $user = $this->createMock(User::class);
        $course = $this->createMock(Course::class);
        $certificate = $this->createMock(Certificate::class);
        
        $certificate->method('getUser')->willReturn($user);
        $certificate->method('getCourse')->willReturn($course);
        $certificate->method('getIssuedAt')->willReturn(new \DateTime());
        
        $this->twig->expects($this->once())
            ->method('render')
            ->with($this->equalTo('pdf/certificate.html.twig'))
            ->willReturn('<html>Certificate</html>');
            
        $this->pdf->expects($this->once())
            ->method('getPdf')
            ->willReturn('PDF_RAW_BINARY');
            
        $result = $this->certificateService->generatePdfContent($certificate);
        
        $this->assertEquals('PDF_RAW_BINARY', $result);
    }
}
