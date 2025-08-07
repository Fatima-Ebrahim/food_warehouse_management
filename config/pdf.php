<?php

return [
    'mode'                  => 'utf-8',
    'format'                => 'A4',
    'author'                => '',
    'subject'               => '',
    'keywords'              => '',
    'creator'               => 'Laravel Pdf',
    'display_mode'          => 'fullpage',
    'tempDir' => storage_path('app/pdf_temp'),
    'pdf_a'                 => false,
    'pdf_a_auto'            => false,
    'icc_profile_path'      => '',


    'font_path' => base_path('public/fonts/'),
    'font_data' => [
        'tajawal' => [
            'R'  => 'Tajawal-Regular.ttf',
            'B'  => 'Tajawal-Bold.ttf',
            'useOTL' => 0xFF,
            'useKashida' => 75,
        ]
    ],

];
