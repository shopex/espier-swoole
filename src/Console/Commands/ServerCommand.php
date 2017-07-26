<?php

namespace Espier\Swoole\Console\Commands;

use Illuminate\Console\Command;

abstract class ServerCommand extends Command
{
    protected function getLockFile($address)
    {
        return sys_get_temp_dir().'/'.strtr($address, '.:', '--').'.pid';
    }

    protected function getProcessId($address)
    {
        $lockFile = $this->getLockFile($address);
        if (file_exists($lockFile)) {
            $processId = file_get_contents($lockFile);
            if (posix_getpgid($processId)) {
                return $processId;
            } else {
                unlink($lockFile);
            }
        }
        return false;
    }

    protected function sendSignal($signal, $address)
    {
        $lockFile = $this->getLockFile($address);
        if ($processId = $this->getProcessId($address)) {
            return posix_kill($processId, $signal);
        } else {
            $this->error(sprintf('No espier web server is listening on http://%s', $address));
            return false;
        }
    }

    protected function isOtherServerProcessRunning($address)
    {
        if ($this->getProcessId($address)) {
            return true;
        }

        list($host, $post) = explode(':', $address);

        $fp = @fsockopen($host, $post, $errno, $errstr, 5);

        if (false !== $fp) {
            fclose($fp);

            return true;
        }

        return false;
    }

    protected function initAddress() {
        $host = config('server.host');
        $port = config('server.port');
        if ($address = $this->argument('address')) {
            if (false == strpos($address, ':')) {
                $address = $address.':'.$port;
            }
            list($host, $port) = explode(':', $address);
        }
        $address = $host.':'.$port;
        var_dump($address);
        app('config')->set('server.host', $host);
        app('config')->set('server.port', $port);

        return [$address, $host, $port];
    }
}
