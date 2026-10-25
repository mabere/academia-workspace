<?php

return [
    'pdftotext_binary' => env('LITSYNTH_PDFTOTEXT_BINARY', 'pdftotext'),
    'max_pdf_kilobytes' => (int) env('LITSYNTH_MAX_PDF_KILOBYTES', 25600),
];
