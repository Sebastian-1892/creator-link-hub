<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

final class GitDeploymentUpdateService
{
    private const FETCH_TIMEOUT = 120.0;

    private const UPDATE_SCRIPT_TIMEOUT = 900.0;

    public function repositoryPath(): string
    {
        $configured = config('creator.app_root');

        return $configured !== null && $configured !== ''
            ? (string) $configured
            : base_path();
    }

    public function isGitDeployment(): bool
    {
        return is_dir($this->repositoryPath().DIRECTORY_SEPARATOR.'.git');
    }

    public function updateScriptPath(): string
    {
        return $this->repositoryPath().DIRECTORY_SEPARATOR.'scripts'.DIRECTORY_SEPARATOR.'update-from-git.sh';
    }

    /**
     * @return array{dirty: bool, summary: string}
     */
    public function workingTreeStatus(): array
    {
        if (! $this->isGitDeployment()) {
            return ['dirty' => false, 'summary' => ''];
        }

        $p = $this->gitProcess(['status', '--porcelain']);
        $p->setTimeout(self::FETCH_TIMEOUT);
        $p->run();

        if (! $p->isSuccessful()) {
            return ['dirty' => false, 'summary' => trim($p->getErrorOutput())];
        }

        $out = trim($p->getOutput());

        return [
            'dirty' => $out !== '',
            'summary' => $out,
        ];
    }

    /**
     * @return array{
     *     ok: bool,
     *     error?: string,
     *     branch?: string,
     *     local_sha?: string,
     *     remote_sha?: string,
     *     local_short?: string,
     *     remote_short?: string,
     *     update_available?: bool
     * }
     */
    public function checkForUpdates(): array
    {
        if (! $this->isGitDeployment()) {
            return ['ok' => false, 'error' => 'no_git'];
        }

        $path = $this->repositoryPath();

        $fetch = $this->gitProcess(['fetch', 'origin']);
        $fetch->setTimeout(self::FETCH_TIMEOUT);
        $fetch->run();

        if (! $fetch->isSuccessful()) {
            return [
                'ok' => false,
                'error' => 'fetch_failed',
                'details' => trim($fetch->getErrorOutput()."\n".$fetch->getOutput()),
            ];
        }

        $branchP = $this->gitProcess(['rev-parse', '--abbrev-ref', 'HEAD']);
        $branchP->run();
        if (! $branchP->isSuccessful()) {
            return ['ok' => false, 'error' => 'branch_failed', 'details' => trim($branchP->getErrorOutput())];
        }

        $branch = trim($branchP->getOutput());
        if ($branch === '') {
            return ['ok' => false, 'error' => 'branch_empty'];
        }

        $localP = $this->gitProcess(['rev-parse', 'HEAD']);
        $localP->run();
        if (! $localP->isSuccessful()) {
            return ['ok' => false, 'error' => 'local_sha_failed', 'details' => trim($localP->getErrorOutput())];
        }

        $localSha = trim($localP->getOutput());

        $remoteRef = 'refs/remotes/origin/'.$branch;
        $remoteP = $this->gitProcess(['rev-parse', $remoteRef]);
        $remoteP->run();

        if (! $remoteP->isSuccessful()) {
            return [
                'ok' => false,
                'error' => 'remote_sha_failed',
                'details' => trim($remoteP->getErrorOutput()),
                'branch' => $branch,
                'local_sha' => $localSha,
            ];
        }

        $remoteSha = trim($remoteP->getOutput());

        return [
            'ok' => true,
            'branch' => $branch,
            'local_sha' => $localSha,
            'remote_sha' => $remoteSha,
            'local_short' => substr($localSha, 0, 7),
            'remote_short' => substr($remoteSha, 0, 7),
            'update_available' => $localSha !== $remoteSha,
        ];
    }

    /**
     * @return array{ok: bool, exit_code?: int, output?: string, error?: string}
     */
    public function runUpdateScript(bool $forceDirty = false): array
    {
        if (! $this->isGitDeployment()) {
            return ['ok' => false, 'error' => 'no_git'];
        }

        $script = $this->updateScriptPath();
        if (! is_file($script) || ! is_readable($script)) {
            return ['ok' => false, 'error' => 'script_missing'];
        }

        $path = $this->repositoryPath();
        $command = ['bash', $script];
        if ($forceDirty) {
            $command[] = '--yes';
        }

        $process = new Process($command, $path, null, null, self::UPDATE_SCRIPT_TIMEOUT);
        $process->run();

        $combined = trim($process->getOutput()."\n".$process->getErrorOutput());
        Log::info('Creator Link Hub: update-from-git.sh finished', [
            'exit' => $process->getExitCode(),
            'output_tail' => strlen($combined) > 4000 ? substr($combined, -4000) : $combined,
        ]);

        return [
            'ok' => $process->isSuccessful(),
            'exit_code' => $process->getExitCode(),
            'output' => $combined,
        ];
    }

    /**
     * @param  list<string>  $args
     */
    private function gitProcess(array $args): Process
    {
        $path = $this->repositoryPath();
        $cmd = array_merge(['git', '-C', $path], $args);

        return new Process($cmd, $path, null, null, self::FETCH_TIMEOUT);
    }
}
