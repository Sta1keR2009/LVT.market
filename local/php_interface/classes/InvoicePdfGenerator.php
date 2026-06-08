<?php

use Dompdf\Dompdf;
use Dompdf\Options;

class InvoicePdfGenerator
{
    public static function generate(array $data, string $filename): string
    {
        $autoloadCandidates = [
            $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/aspro.lite/vendors/dompdf/vendor/autoload.php',
            $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/aspro.max/vendors/dompdf/vendor/autoload.php',
        ];

        $autoloadFound = false;
        foreach ($autoloadCandidates as $autoload) {
            if (is_readable($autoload)) {
                require_once $autoload;
                $autoloadFound = true;
                break;
            }
        }

        if (!$autoloadFound || !class_exists('\Dompdf\Dompdf')) {
            throw new RuntimeException('Dompdf autoload not found');
        }

        $templatePath = $_SERVER['DOCUMENT_ROOT'] . '/local/templates/invoice/invoice_template.php';
        if (!is_readable($templatePath)) {
            throw new RuntimeException('Invoice template not found');
        }

        ob_start();
        $data = $data; // for template scope
        include $templatePath;
        $html = (string)ob_get_clean();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4');
        $dompdf->render();

        $tmpDir = $_SERVER['DOCUMENT_ROOT'] . '/upload/invoices/tmp';
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $filename);
        $pdfPath = $tmpDir . '/' . $safeName . '.pdf';
        file_put_contents($pdfPath, $dompdf->output());

        return $pdfPath;
    }
}
