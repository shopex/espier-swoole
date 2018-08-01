<?php

namespace Espier\Swoole\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class StopCommand extends ServerCommand
{
    protected $name = 'espier:stop';

    protected $description = 'Stops Espier web server that was started with the espier:stop command';

    public function configure()
    {
        $this->setHelp(<<<EOF
The <info>%command.name%</info> stops espier web server:

  <info>php %command.full_name%</info>

To change the default bind address and the default port use the <info>address</info> argument:

  <info>php %command.full_name% 0.0.0.0:9058</info>

EOF
        );
    }

    public function handle()
    {
        list($address, $host, $port) = $this->initAddress();

        if ($this->sendSignal(SIGTERM, $address))
        {
            unlink($this->getLockFile($address));

            $this->info(sprintf('Stopped the espier web server listening on http://%s', $address));
        } else {
            return 1;
        }
    }

    protected function getOptions()
    {
        return [
            ['port', 'p', InputOption::VALUE_REQUIRED, 'Address port number', '9058'],
        ];
    }

    protected function getArguments()
    {
        return [
            ['address', InputArgument::OPTIONAL, 'Address:port']
        ];
    }
}
