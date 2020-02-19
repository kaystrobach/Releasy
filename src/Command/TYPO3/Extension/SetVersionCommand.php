<?php

namespace KayStrobach\Releasy\Command\TYPO3\Extension;

use KayStrobach\Releasy\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetVersionCommand extends AbstractCommand
{
    protected static $defaultName = 'typo3:extension:setversion';

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
        $emConf = $input->getArgument('path') . DIRECTORY_SEPARATOR . 'ext_emconf.php';
        if (!file_exists($emConf)) {
            $output->writeln('<info>❌  File ' . $emConf . ' is missing</info>');
            throw new \InvalidArgumentException('File ' . $emConf . ' is missing');
        }

        $content = preg_replace(
            '#\'version\' => \'[^\']*\'#',
            '\'version\' => \'' . $input->getArgument('version') . '\'',
            file_get_contents($emConf)
        );
        file_put_contents($emConf, $content, LOCK_EX);
        $output->writeln('<info>✅  Version in file ' . $emConf . ' is set to ' . $input->getArgument('version') . '</info>');
    }
}