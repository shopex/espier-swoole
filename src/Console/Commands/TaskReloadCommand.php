<?php

namespace Espier\Swoole\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class TaskReloadCommand extends Command
{
    protected $name = 'espier:task_reload';

    protected $description = 'Reload espier web server\'s tasks that was started with the espier:task_reload command';

    public function configure()
    {
        $this->setHelp(<<<EOF
The <info>%command.name%</info> reloads espier web server's :

  <info>php %command.full_name%</info>

To change the default bind address and the default port use the <info>address</info> argument:

  <info>php %command.full_name% 127.0.0.1:9058</info>

EOF
        );
    }

    public function fire()
    {
        $address = $input->getArgument('address');
        if (false === strpos($address, ':')) {
            $address = $address.':'.$input->getOption('port');
        }

        if ($this->sendSignal(SIGUSR2, $address)) {

            $this->info(sprintf('Reload the espier web server listening on http://%s', $address));

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
            ['address', InputArgument::OPTIONAL, 'Address:port', '127.0.0.1']
        ];
    }
}