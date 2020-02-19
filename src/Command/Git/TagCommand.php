<?php
namespace KayStrobach\Releasy\Command\Git;

use KayStrobach\Releasy\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TagCommand extends AbstractCommand
{
    protected static $defaultName = 'git:tag';

    protected function configure()
    {
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Creates a new release and pushes it to the server')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command updates all included extension versions pushes the command to the server')
            ->addArgument('version', InputArgument::REQUIRED, 'version or tag name')
        ;
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    )
    {
        $version = $input->getArgument('version');

        $this->execCommand('git add *', $output);
        $this->execCommand('git commit -am "[RELEASE] ' . $version . '"', $output);
        $this->execCommand('git tag -a ' . $version . ' -m ' . $version, $output);

        $output->writeln('<info>✅  Tagged the release, please push it now, and do not forget to st the release notes</info>');
    }
}