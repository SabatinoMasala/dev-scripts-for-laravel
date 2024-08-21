<?php

return [
    'scripts' => [
        'queue' => [
            'command' => ['php', 'artisan', 'queue:work'],
            'style' => ['cyan', null, ['bold']],
            'logging' => true,
            'restart' => [
                'watch' => [
                    '.env',
                    'app/Jobs/*'
                ]
            ]
        ]
    ]
];
