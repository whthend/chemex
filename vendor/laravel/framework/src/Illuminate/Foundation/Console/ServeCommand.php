<?php

namespace Illuminate\Foundation\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Env;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

#[AsCommand(name: 'serve')]
class ServeCommand extends Command
{
    /**
     * The environment variables that should be passed from host machine to the PHP server process.
     *
     * @var string[]
     */
    public static $passthroughVariables = [
        'APP_ENV',
        'LARAVEL_SAIL',
        'PHP_CLI_SERVER_WORKERS',
        'PHP_IDE_CONFIG',
        'SYSTEMROOT',
        'XDEBUG_CONFIG',
        'XDEBUG_MODE',
        'XDEBUG_SESSION',
    ];
    /**
     * The name of the console command.
     *
     * This name is used to identify the command during lazy loading.
     *
     * @var string|null
     *
     * @deprecated
     */
    protected static $defaultName = 'serve';
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'serve';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Serve the application on the PHP development server';
    /**
     * The current port offset.
     *
     * @var int
     */
    protected $portOffset = 0;

    /**
     * Execute the console command.
     *
     * @return int
     *
     * @throws \Exception
     */
    public function handle()
    {
        $this->line("<info>Starting Laravel development server:</info> http://{$this->host()}:{$this->port()}");

        $environmentFile = $this->option('env')
            ? base_path('.env') . '.' . $this->option('env')
            : base_path('.env');

        $hasEnvironment = file_exists($environmentFile);

        $environmentLastModified = $hasEnvironment
            ? filemtime($environmentFile)
            : now()->addDays(30)->getTimestamp();

        $process = $this->startProcess($hasEnvironment);

        while ($process->isRunning()) {
            if ($hasEnvironment) {
                clearstatcache(false, $environmentFile);
            }

            if (!$this->option('no-reload') &&
                $hasEnvironment &&
                filemtime($environmentFile) > $environmentLastModified) {
                $environmentLastModified = filemtime($environmentFile);

                $this->comment('Environment modified. Restarting server...');

                $process->stop(5);

                $process = $this->startProcess($hasEnvironment);
            }

            usleep(500 * 1000);
        }

        $status = $process->getExitCode();

        if ($status && $this->canTryAnotherPort()) {
            $this->portOffset += 1;

            return $this->handle();
        }

        return $status;
    }

    /**
     * Get the host for the command.
     *
     * @return string
     */
    protected function host()
    {
        [$host,] = $this->getHostAndPort();

        return $host;
    }

    /**
     * Get the host and port from the host option string.
     *
     * @return array
     */
    protected function getHostAndPort()
    {
        $hostParts = explode(':', $this->input->getOption('host'));

        return [
            $hostParts[0],
            $hostParts[1] ?? null,
        ];
    }

    /**
     * Get the port for the command.
     *
     * @return string
     */
    protected function port()
    {
        $port = $this->input->getOption('port');

        if (is_null($port)) {
            [, $port] = $this->getHostAndPort();
        }

        $port = $port ?: 8000;

        return $port + $this->portOffset;
    }

    /**
     * Start a new server process.
     *
     * @param bool $hasEnvironment
     * @return \Symfony\Component\Process\Process
     */
    protected function startProcess($hasEnvironment)
    {
        $process = new Process($this->serverCommand(), public_path(), collect($_ENV)->mapWithKeys(function ($value, $key) use ($hasEnvironment) {
            if ($this->option('no-reload') || !$hasEnvironment) {
                return [$key => $value];
            }

            return in_array($key, static::$passthroughVariables) ? [$key => $value] : [$key => false];
        })->all());

        $process->start(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        return $process;
    }

    /**
     * Get the full server command.
     *
     * @return array
     */
    protected function serverCommand()
    {
        $server = file_exists(base_path('server.php'))
            ? base_path('server.php')
            : __DIR__ . '/../resources/server.php';

        return [
            (new PhpExecutableFinder)->find(false),
            '-S',
            $this->host() . ':' . $this->port(),
            $server,
        ];
    }

    /**
     * Check if the command has reached its maximum number of port tries.
     *
     * @return bool
     */
    protected function canTryAnotherPort()
    {
        return is_null($this->input->getOption('port')) &&
            ($this->input->getOption('tries') > $this->portOffset);
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['host', null, InputOption::VALUE_OPTIONAL, 'The host address to serve the application on', Env::get('SERVER_HOST', '127.0.0.1')],
            ['port', null, InputOption::VALUE_OPTIONAL, 'The port to serve the application on', Env::get('SERVER_PORT')],
            ['tries', null, InputOption::VALUE_OPTIONAL, 'The max number of ports to attempt to serve from', 10],
            ['no-reload', null, InputOption::VALUE_NONE, 'Do not reload the development server on .env file changes'],
        ];
    }
}
