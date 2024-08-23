<?php

namespace SabatinoMasala\DevScriptsForLaravel\Commands;

use Dotenv\Dotenv;
use Illuminate\Console\Command;
use Illuminate\Support\Sleep;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class RunProcessWithWatcher extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:process-with-watcher {key} {config}';

    protected $process;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run process';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $key = $this->argument('key');
        $input = json_decode($this->argument('config'), true);
        $workingDirectory = $input['working_directory'] ?? null;

        // Check if there is a .env file in the working directory, if so load that one instead of the default
        $envDirectory = base_path();
        if ($workingDirectory && file_exists($workingDirectory . '/.env')) {
            $envDirectory = $workingDirectory;
        }
        $env = Dotenv::createArrayBacked($envDirectory)->load();

        $this->process = new Process($input['command'], $workingDirectory, $env);
        $this->process->start();

        $watcher = null;
        if (!empty($input['restart']) && !empty($input['restart']['watch'])) {
            $watcher = $this->getWatchProcess($input['restart']['watch'], $workingDirectory, $env);
        }

        while (true) {
            if ($watcher && $watcher->isRunning()) {
                $lines = explode(PHP_EOL, $watcher->getIncrementalOutput());
                $lines = array_filter($lines);
                collect($lines)->each(function($line) use ($key, $input, $envDirectory) {
                    if (!empty(trim($line))) {
                        if ($input['restart']['logging'] === true) {
                            $this->comment('Restarting ' . $key . ' due to event: ' . $line);
                        }
                        $this->process->stop();
                        // .env might've been updated
                        $this->process->setEnv(Dotenv::createArrayBacked($envDirectory)->load());
                        $this->process = $this->process->restart();
                    }
                });
            }
            if ($this->process->isRunning() && $input['logging']) {
                $output = $this->process->getIncrementalOutput();
                $errorOutput = $this->process->getIncrementalErrorOutput();
                if (!empty($output)) {
                    $output = explode(PHP_EOL, $output);
                    collect($output)->filter()->each(function($output) use ($key) {
                        $this->line(trim($output));
                    });
                }
                if (!empty($errorOutput)) {
                    $this->error(trim($errorOutput));
                }
            }
            Sleep::for(0.1)->seconds();
        }
    }

    protected function getWatchProcess($paths, $workingDirectory = null, $env = null): Process
    {
        $command = [
            (new ExecutableFinder)->find('node'),
            realpath(__DIR__ . '/../../bin/file-watcher.cjs'),
            json_encode($paths),
        ];

        \Log::info('Watching for changes on ' . json_encode($paths));
        \Log::info($command);

        $process = new Process(
            command: $command,
            timeout: null,
        );

        if ($workingDirectory) {
            $process->setWorkingDirectory($workingDirectory);
        }

        if ($env) {
            $process->setEnv($env);
        }

        $process->start();

        return $process;
    }

}
