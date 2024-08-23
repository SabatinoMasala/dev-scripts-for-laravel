# Dev scripts for Laravel

Run multiple scripts in parallel from a single command, with integrated logging.

## Installation

```
composer require sabatinomasala/dev-scripts-for-laravel
```

This package publishes a `dev-scripts` config file:

```
php artisan vendor:publish --tag=dev-services
```

## Configuration

Define your scripts in the `dev-scripts.php` config file, eg:

```php
<?php

return [
    'scripts' => [
        'horizon' => [
            'command' => ['php', 'artisan', 'horizon'],
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
        ],
        'reverb' => [
            'command' => ['php', 'artisan', 'reverb:start', '--verbose', '--debug'],
            'style' => ['magenta', null, ['bold']],
            'logging' => true,
        ],
    ]
];
```

## Usage

Run the scripts with the `php artisan dev` command:

```
php artisan dev
```

## File watcher

The `restart.watch` option allows you to define a list of directories/files to watch. Changes to these files will trigger a restart of the script.
This feature is powered by the Javascript file watcher Chokidar, you can install it as follows:

NPM:

```
npm install chokidar
```

Yarn:
```
yarn add chokidar
```

## Logging

You can enable logging for each script by setting the `logging` option to `true`.
Logs will be shown in the console and will be prefixed with the script name.
To make the logs more readable, you can define a style for each script.

```
[
    'style' => ['magenta', null, ['bold']],
]
```

## Log options

```
'log_options' => [
    // Apply the style to the full line, not just the script name
    'apply_style_to_full_line' => true
],
```

These parameters are passed into the constructor of `Symfony\Component\Console\Formatter\OutputFormatterStyle`, you can refer to the documentation here:
https://symfony.com/doc/current/console/coloring.html

## Working Directory

You can change the working directory for each script by setting the `working_directory` option.
When combining the `working_directory` option with the `restart.watch` option, the paths in the `restart.watch` option should be relative to the `working_directory`.

```
'working_directory' => 'path/to/working/directory',
```

### ENV:

By default, all scripts will run with the same environment variables as the parent process.
When using the `working_directory` option, you can define a custom `.env` file in that directory to be used for that process instead of the default environment variables.

## Credits

file-watcher: https://github.com/spatie/file-system-watcher/blob/main/bin/file-watcher.js
