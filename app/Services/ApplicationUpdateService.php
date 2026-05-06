<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

final class ApplicationUpdateService
{
    private const UPDATE_SCRIPT_TIMEOUT = 900.0;

    public function repositoryPath(): string
    {
        $configured = config('creator.app_root');

        return $configured !== null && $configured !== ''
            ? (string) $configured
            : base_path();
    }

    public function updateScriptPath(): string
    {
        return $this->repositoryPath().DIRECTORY_SEPARATOR.'scripts'.DIRECTORY_SEPARATOR.'update-application.sh';
    }

    public function isUpdateScriptAvailable(): bool
    {
        $path = $this->updateScriptPath();

        return is_file($path) && is_readable($path);
    }

    /**
     * @return array{ok: bool, exit_code?: int, output?: string, error?: string}
     */
    public function runUpdateScript(): array
    {
        if (! $this->isUpdateScriptAvailable()) {
            return ['ok' => false, 'error' => 'script_missing'];
        }

        $script = $this->updateScriptPath();
        $path = $this->repositoryPath();
        $command = ['bash', $script];

        $process = new Process($command, $path, null, null, self::UPDATE_SCRIPT_TIMEOUT);
        $process->run();

        $combined = trim($process->getOutput()."\n".$process->getErrorOutput());
        Log::info('Creator Link Hub: update-application.sh finished', [
            'exit' => $process->getExitCode(),
            'output_tail' => strlen($combined) > 4000 ? substr($combined, -4000) : $combined,
        ]);

        return [
            'ok' => $process->isSuccessful(),
            'exit_code' => $process->getExitCode(),
            'output' => $combined,
        ];
    }
}
