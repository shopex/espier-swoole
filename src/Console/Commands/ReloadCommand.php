<?php

namespace Espier\Swoole\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;


class ReloadCommand extends ServerCommand
{
    protected $name = 'espier:reload';

    protected $description = 'Reloads espier web server that was started with the espier:reload command';

    public function configure()
    {
        $this->setHelp(<<<EOF
The <info>%command.name%</info> reloads espier web server:

  <info>php %command.full_name%</info>

To change the default bind address and the default port use the <info>address</info> argument:

  <info>php %command.full_name% 0.0.0.0:9058</info>

EOF
        );
    }

    public function handle()
    {
        list($address, $host, $port) = $this->initAddress();

        if ($this->sendSignal(SIGUSR1, $address)) {

            $this->info(sprintf('Reload the espier web server listening on http://%s', $address));

        } else {
            return 1;
        }
    }

    protected function getArguments()
    {
        return [
            ['address', InputArgument::OPTIONAL, 'Address:port']
        ];
    }
}
