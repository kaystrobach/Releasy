<?php

namespace KayStrobach\Releasy\Command;

use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use ZipArchive;

class PackageCommand extends Command
{
    protected static $defaultName = 'package';

    protected function configure()
    {
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Package an extension')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('packages a given extension after doing an composer install in the vendor dir if needed')
            ->addArgument('path', InputArgument::REQUIRED, 'path to the extension')
            ->addArgument('name', InputArgument::REQUIRED, 'name of the extension')
            ->addArgument('output', InputArgument::REQUIRED, 'path of the Builddir')
        ;
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    )
    {
        exec('mkdir -p ' . $input->getArgument('output'));

        $path = $input->getArgument('path') . '/' . $input->getArgument('name') . '/';
        $cwd = getcwd();

        if (!file_exists($path . 'ext_emconf.php')) {
            throw new InvalidArgumentException($path . ' does not contain an ext_emconf.php');
        }

        if (!file_exists($path . 'composer.json')) {
            throw new InvalidArgumentException($path . ' does not contain an composer.json');
        }

        $composerPath = $path . 'Resources/Private/PHP/';
        if (file_exists($composerPath . 'composer.json')) {
            $output->writeln('Found composer.json, start packaging');
            chdir($composerPath);
            exec('composer install --prefer-dist --no-dev');
        }

        chdir($cwd);
        $symfonyFinder = new Finder();
        $files = $symfonyFinder->in($path)->name('*')->files();

        $output->writeln('Create Zip file: ' . $cwd . '/' . $input->getArgument('name') .'.zip');

        $zip = new ZipArchive();
        $zip->open($input->getArgument('output') . '/' . $input->getArgument('name') .'.zip', ZIPARCHIVE::CREATE);

        /** @var \SplFileInfo $file */
        foreach ($files as $file) {
            $localFileName = substr($file->getPathname(), strlen($path));
            $zip->addFile($file->getPathname(), $localFileName);
            $output->writeln(' + ' . $localFileName);
        }
        $zip->close();

        chdir($cwd);
    }
}
