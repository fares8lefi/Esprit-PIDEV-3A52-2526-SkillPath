<?php

namespace App\Service;

use Nucleos\DompdfBundle\Wrapper\DompdfWrapperInterface;
use Twig\Environment;
use App\Entity\Certificate;

class CertificateService
{
    private $twig;
    private $pdf;

    public function __construct(Environment $twig, DompdfWrapperInterface $pdf)
    {
        $this->twig = $twig;
        $this->pdf = $pdf;
    }

    public function generatePdfContent(Certificate $certificate): string
    {
        // Generate HTML with Twig
        $html = $this->twig->render('pdf/certificate.html.twig', [
            'certificate' => $certificate,
            'user' => $certificate->getUser(),
            'course' => $certificate->getCourse(),
            'date' => $certificate->getIssuedAt()->format('d/m/Y')
        ]);

        // Generate PDF
        return $this->pdf->getPdf($html, ['isRemoteEnabled' => true, 'defaultFont' => 'Arial']);
    }
}
