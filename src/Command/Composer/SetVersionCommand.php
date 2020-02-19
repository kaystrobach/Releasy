<?php

namespace KayStrobach\Releasy\Command\Composer;

use KayStrobach\Releasy\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetVersionCommand extends AbstractCommand
{
    protected static $defaultName = 'composer:setversion';

    protected function configure()
    {
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Sets a new version of a TYPO3 extension.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This commands sets the version of a TYPO3 extension by modifying the ext_emconf.php')
            ->addArgument('path', InputArgument::REQUIRED, 'path to the root directory of the TYPO3 extension')
            ->addArgument('version', InputArgument::REQUIRED, 'version or tag name')
        ;
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    )
    {
        $composerJson = $input->getArgument('path') . DIRECTORY_SEPARATOR . 'composer.json';
        if (!file_exists($composerJson)) {
            $output->writeln('<info>❌  File ' . $composerJson . ' is missing</info>');
            throw new \InvalidArgumentException('File ' . $composerJson . ' is missing');
        }

        $content = json_decode(file_get_contents($composerJson), true);
        $content['version'] = $input->getArgument('version');
        file_put_contents(
            $composerJson,
            json_encode($content,JSON_PRETTY_PRINT),
            LOCK_EX
        );
        $output->writeln('<info>✅  Version in file ' . $composerJson . ' is set to ' . $input->getArgument('version') . '</info>');
    }
}