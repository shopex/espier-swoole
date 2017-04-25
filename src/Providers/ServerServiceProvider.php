<?php

namespace Espier\Swoole\Providers;

use Illuminate\Support\ServiceProvider;
use Espier\Swoole\Console\Commands\StartCommand;
use Espier\Swoole\Console\Commands\RestartCommand;
use Espier\Swoole\Console\Commands\ReloadCommand;
use Espier\Swoole\Console\Commands\StatusCommand;
use Espier\Swoole\Console\Commands\StopCommand;
use Espier\Swoole\Console\Commands\TaskReloadCommand;

class ServerServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfig();
        $this->registerConsoleCommands();
    }

    public function registerConsoleCommands()
    {
        $this->commands(
            StartCommand::class,
            StopCommand::class,
            RestartCommand::class,
            ReloadCommand::class,
            StatusCommand::class,
            TaskReloadCommand::class
        );
    }

    protected function mergeConfig()
    {
        $this->app->configure('server');
        $this->mergeConfigFrom(
            $this->getConfigPath(), 'server'
        );
    }

    protected function getConfigPath()
    {
        return __DIR__.'/../../config/server.php';
    }
}