<?php

namespace KayStrobach\Releasy\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PharCommand extends Command
{
    protected static $defaultName = 'phar';

    protected function configure()
    {
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Creates a phar')
            ->addArgument('path', InputArgument::REQUIRED, 'path to the extension')
            ->addArgument('filename', InputArgument::REQUIRED, 'name of the phar')
            ->addArgument('version', InputArgument::REQUIRED, 'version or tag name')
            ->addArgument('defaultStub', InputArgument::REQUIRED, 'default stub')
            ->addArgument('defaultStubWeb', InputArgument::OPTIONAL, 'default stub web', null)
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command creates a phar archive')

        ;
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    )
    {
        if (ini_get('phar.readonly') === '1') {
            $output->writeln('<info>❌  PHP Option phar.readonly=1 is set, relaunching with option set to 0</info>');
            system(
                implode(
                    ' ',
                    [
                        'php -d phar.readonly=0 ./release',
                        'phar',
                        $input->getArgument('path'),
                        $input->getArgument('filename'),
                        $input->getArgument('version'),
                        $input->getArgument('defaultStub'),
                        $input->getArgument('defaultStubWeb')
                    ]
                )
            );
            return;
        }
        $output->writeln('<info>✅  PHP Option phar.readonly=0 is set, lets create a phar</info>');

        $phar = new \Phar($input->getArgument('filename'));
        $files = $phar->buildFromDirectory($input->getArgument('path'));
        $phar->setDefaultStub(
            $input->getArgument('defaultStub'),
            $input->getArgument('defaultStubWeb')
        );
    }
}