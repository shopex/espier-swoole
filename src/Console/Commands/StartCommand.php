<?php

namespace Espier\Swoole\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Espier\Swoole\Server;


class StartCommand extends ServerCommand
{

    protected $name = 'espier:start';

    protected $description = 'Starts espier web server in the background';

    public function configure()
    {
        $this->setHelp(<<<EOF
The <info>%command.name%</info> runs espier web server:
  <info>php %command.full_name%</info>
EOF
        );
    }
    
    public function fire()
    {
        
        list($address, $host, $port) = $this->initAddress();

        if ($this->isOtherServerProcessRunning($address)) {
            if ($this->option('force')) {
                return $this->call('espier:restart', [
                    'address' => $address
                ]);
            } else {
                $this->error(sprintf('A process is already listening http://%s', $address));
                $this->error('Use the --force option if the server terminated unexpectedly to start a new web server process.');

                return 1;
            }
        }
        
        $this->info(sprintf('Espier web server listening on http://%s', $address));

        $server = new Server($this->getLaravel(), $this->getLockFile($address), []);

        $server->run();
    }
    
    protected function getOptions()
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Force web server startup']
        ];
    }

    protected function getArguments()
    {
        return [
            ['address', InputArgument::OPTIONAL]
        ];
    }

}