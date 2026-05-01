<?php

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;

class PDFService
{
    protected $dompdf;

    public function __construct()
    {
        $this->dompdf = new Dompdf();
        
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('isRemoteEnabled', true);
        
        $this->dompdf->setOptions($options);
    }

    public function generatePDF($html, $filename = 'document.pdf')
    {
        $this->dompdf->loadHtml($html);
        $this->dompdf->setPaper('A4', 'portrait');
        $this->dompdf->render();

        return $this->dompdf->stream($filename, [
            'Attachment' => false
        ]);
    }

    public function generatePDFAttachment($html, $filename = 'document.pdf')
    {
        $this->dompdf->loadHtml($html);
        $this->dompdf->setPaper('A4', 'portrait');
        $this->dompdf->render();

        return $this->dompdf->stream($filename, [
            'Attachment' => true
        ]);
    }
}



















