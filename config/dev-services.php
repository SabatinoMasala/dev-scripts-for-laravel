<?php

return [
    'scripts' => [
        'horizon' => [
            'command' => ['php', 'artisan', 'queue:work'],
            'working_directory' => null,
            'style' => ['cyan', null, ['bold']],
            'logging' => true,
            'log_options' => [
                'apply_style_to_full_line' => true
            ],
            'restart' => [
                'logging' => true,
                'watch' => [
                    '.env',
                    'app/Jobs/*'
                ]
            ]
        ]
    ]
];
