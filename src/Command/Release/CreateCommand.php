<?php

namespace KayStrobach\Releasy\Command\Release;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use KayStrobach\Releasy\Command\AbstractCommand;

class CreateCommand extends AbstractCommand
{
    protected static $defaultName = 'release:create';

    protected function configure()
    {
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Creates a new release and pushes it to the server')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command updates all included extension versions pushes the command to the server')
            ->addArgument('version', InputArgument::REQUIRED, 'version or tag name')
            ->addArgument('path', InputArgument::OPTIONAL, 'path', '.')
        ;
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    )
    {
        #if (!$this->isCommandOk('git diff --exit-code')) {
        #    $output->writeln('<error>Please ensure you have committed all your changes!</error>');
        #    return 10;
        #}

        $version = $input->getArgument('version');
        $output->writeln('Preparing to release ' . $version);

        $finder = new Finder();
        $finder->in($input->getArgument('path') . DIRECTORY_SEPARATOR . 'DistributionPackages')->name('ext_emconf.php');

        foreach($finder->getIterator() as $item)
        {
            $this->callInternalCommand(
                'typo3:extension:setversion',
                [
                    'path' => $item->getPath(),
                    'version' => $input->getArgument('version')
                ],
                $output
            );
            $this->callInternalCommand(
                'composer:setversion',
                [
                    'path' => $item->getPath(),
                    'version' => $input->getArgument('version')
                ],
                $output
            );
        }

        $this->callInternalCommand(
            'release:updatechangelog',
            [
                'version' => $input->getArgument('version'),
                'path' => $input->getArgument('path'),
            ],
            $output
        );

        if ($this->confirm('Create git tag now?', $input, $output)) {
            $this->callInternalCommand(
                'git:tag',
                [
                    'version' => $input->getArgument('version'),
                ],
                $output
            );
        } else {
            $output->writeln('<info>❌  No git tag created</info>');
        }
        return 0;
    }
}
