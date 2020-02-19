<?php

namespace KayStrobach\Releasy\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Finder\Finder;

class ExtensionListCommand extends Command
{
    protected static $defaultName = 'extlist';

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $exts = $this->getExtensionInformation();
        $output->write($this->renderDocs($exts, $output));
    }

    protected function getExtensionInformation()
    {
        $finder = new Finder();
        $files = $finder->files()->in('DistributionPackages')->name('composer.json');
        $EM_CONF = [];
        /** @var \Iterator|\SplFileInfo $file */
        foreach ($files->getIterator() as $file) {
            $_EXTKEY = basename($file->getPath());
            $EM_CONF[$_EXTKEY] = json_decode(
                file_get_contents(
                    $file->getPathname()
                ),
                true
            );
        }
        return $EM_CONF;
    }

    protected function renderDocs($extensions, OutputInterface $output)
    {
        $buffer = '.. only:: html' . PHP_EOL . PHP_EOL;
        foreach ($extensions as $key => $extension) {
            $buffer .= '	:' . $key . ':' . PHP_EOL;
            $buffer .= '		' . $extension['description'] . ':' . PHP_EOL . PHP_EOL;
        }
        $buffer .= PHP_EOL;
        return $buffer;
    }
}
