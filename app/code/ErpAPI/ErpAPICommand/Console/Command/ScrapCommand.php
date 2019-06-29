<?php

namespace ErpAPI\ErpAPICommand\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class HelloWorldCommand.
 */
class ScrapCommand extends Command
{


    protected function configure()
    {
        $this->setName('erpapi:scrap')->setDescription('So much hello world.');

        parent::configure();
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        dump(__FILE__);

        $output->writeln('Hello World!');
    }
}
