<?php

namespace SabatinoMasala\DevScriptsForLaravel\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Sleep;
use Symfony\Component\Console\Command\SignalableCommandInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Process\Process;

class DevServices extends Command implements SignalableCommandInterface
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev';
    public $shouldExit = false;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Starts dev services';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $processes = config('dev-services.scripts');

        $processes = collect($processes)->mapWithKeys(function($input, $key) {
            $this->info('Starting ' . $key);
            $snakeKey = \Str::snake($key);
            $style = new OutputFormatterStyle(...$input['style']);
            $this->output->getFormatter()->setStyle($snakeKey, $style);
            $process = new Process(['php', 'artisan', 'run:process-with-watcher', $key, json_encode($input)]);
            $process->start();
            return [
                $snakeKey => [
                    'process' => $process,
                    'logging' => $input['logging'],
                    'log_options' => !empty($input['log_options']) ? $input['log_options'] : [],
                ]
            ];
        });

        while (true) {
            if ($this->shouldExit) {
                break;
            }
            $processes->each(function($input, $key) {
                $process = $input['process'];
                if ($process->isRunning() && $input['logging']) {
                    $logOptions = [
                        'apply_style_to_full_line' => $input['log_options']['apply_style_to_full_line'] ?? false,
                    ];
                    $output = $process->getIncrementalOutput();
                    $errorOutput = $process->getIncrementalErrorOutput();
                    if (!empty($output)) {
                        $output = explode(PHP_EOL, $output);
                        collect($output)->filter()->each(function($output) use ($key, $logOptions) {
                            if ($logOptions['apply_style_to_full_line']) {
                                $this->line("<$key>$key: " . trim($output) . "</$key>", $key);
                            } else {
                                $this->line("<$key>$key</$key>: " . trim($output));
                            }
                        });
                    }
                    if (!empty($errorOutput)) {
                        $this->error(trim($errorOutput));
                    }
                }
            });
            Sleep::for(1)->seconds();
        }

        $processes->each(function($input, $key) {
            $process = $input['process'];
            if ($process->isRunning()) {
                $process->signal(SIGINT);
            }
        });
    }

    public function getSubscribedSignals(): array
    {
        return [
            SIGINT,
            SIGTERM,
        ];
    }

    public function handleSignal(int $signal, false|int $previousExitCode = 0): int|false
    {
        $this->shouldExit = true;
        return false;
    }

}
