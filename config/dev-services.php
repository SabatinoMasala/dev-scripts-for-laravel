<?php

return [
    'scripts' => [
        'horizon' => [
            'command' => ['php', 'artisan', 'queue:work'],
            'style' => ['cyan', null, ['bold']],
            'logging' => true,
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
