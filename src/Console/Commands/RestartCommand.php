<?php

namespace Espier\Swoole\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;


class RestartCommand extends ServerCommand
{
    protected $name = 'espier:restart';

    protected $description = 'Restarts espier web server that was started with the espier:restart command';

    public function configure()
    {
        $this->setHelp(<<<EOF
The <info>%command.name%</info> restarts espier web server:

  <info>php %command.full_name%</info>

To change the default bind address and the default port use the <info>address</info> argument:

  <info>php %command.full_name% 127.0.0.1:9058</info>

EOF
        );
    }

    public function fire()
    {
        $address = $this->argument('address');
        if (false === strpos($address, ':')) {
            $address = $address.':'.$this->option('port');
        }

        // reload
        if ($this->sendSignal(SIGTERM, $address)) {
            usleep(1*1000000);

            if ($this->getProcessId($address)) {
                usleep(3*1000000);

                if ($this->getProcessId($address)) {
                    return 1;
                }
            }

            $this->info(sprintf('Stopped the espier web server listening on http://%s', $address));

            return $this->call('espier:start', [
                'address' => $this->argument('address'),
                '--port' => $this->option('port')
            ]);
        
        } else {
            return 1;
        }
    }

    protected function getOptions()
    {
       return [
            ['port', 'p', InputOption::VALUE_REQUIRED, 'Address port number', '9058'],
            ['force', 'f', InputOption::VALUE_NONE, 'Force web server startup']
        ];
    }

    protected function getArguments()
    {
        return [
            ['address', InputArgument::OPTIONAL, 'Address:port', '127.0.0.1']
        ];
    }
}