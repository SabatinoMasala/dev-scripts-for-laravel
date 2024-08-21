<?php

return [
    'scripts' => [
        'horizon' => [
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
