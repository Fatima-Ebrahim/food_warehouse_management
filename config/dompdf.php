<?php

return [

    'show_warnings' => false,

    'orientation' => 'portrait',

    'defines' => [
        'font_dir' => resource_path('fonts/'),
        'font_cache' => storage_path('fonts/'),
        'temp_dir' => sys_get_temp_dir(),
        'chroot' => realpath(base_path()),

        'log_output_file' => storage_path('logs/dompdf.html'),

        'default_media_type' => 'screen',
        'default_paper_size' => 'a4',
        'default_font' => 'amiri', // <-- تم التعديل هنا

        'dpi' => 96,
        'enable_php' => false,
        'enable_javascript' => true,
        'enable_remote' => true,
        'font_height_ratio' => 1.1,
        'enable_font_subsetting' => false,

        'pdf_backend' => 'CPDF',
        'default_font_size' => 12,

        'font_family' => [
            'amiri' => [
                'R' => 'Amiri-Regular.ttf',
                'B' => 'Amiri-Bold.ttf',
                'I' => 'Amiri-Italic.ttf',
                'BI' => 'Amiri-BoldItalic.ttf',
            ],
        ],
    ],
];
