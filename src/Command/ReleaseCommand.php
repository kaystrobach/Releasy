<?php

namespace KayStrobach\Releasy\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Finder\Finder;

class ReleaseCommand extends Command
{
    protected static $defaultName = 'release';

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
        if (!$this->isCommandOk('git diff --exit-code')) {
            $output->writeln('<error>Please ensure you have committed all your changes!</error>');
            return 10;
        }

        $version = $input->getArgument('version');
        $output->writeln('Preparing to release ' . $version);

        $finder = new Finder();
        $finder->in(__DIR__ . '/../../DistributionPackages')->name('ext_emconf.php');

        foreach($finder->getIterator() as $item)
        {
            $content = preg_replace(
                '#\'version\' => \'[^\']*\'#',
                '\'version\' => \'' . $version . '\'',
                $item->getContents()
            );
            file_put_contents($item->getPathname(), $content);
        }

        #$content = $this->requestText(
        #    'Would you like to add a release description?',
        #    $input,
        #    $output
        #);

        $content = PHP_EOL . '* '
        . implode(
            PHP_EOL . '* ',
            $this->getCommandOutput(
                'git log $(git describe --tags --abbrev=0)..HEAD --pretty=format:"%h %s"',
                $output
            )
        );

        $changelogPath = __DIR__ . '/../../Changelog.md';

        file_put_contents(
            $changelogPath,
            '# Version ' . $version . PHP_EOL
                . $content . PHP_EOL
                . PHP_EOL
                . file_get_contents($changelogPath)
        );

        if (!$this->confirm('Commit and release the new version?', $input, $output)) {
            return 1;
        }

        $this->execCommand('git add *', $output);
        $this->execCommand('git commit -am "[RELEASE] ' . $version . '"', $output);
        $this->execCommand('git tag -a ' . $version . ' -m ' . $version, $output);

        return 0;
    }

    protected function isCommandOk(string $cmd): bool
    {
        $output = [];
        $exitCode = 0;
        exec($cmd, $output, $exitCode);

        return $exitCode === 0;
    }

    protected function execCommand(string $cmd, OutputInterface $outputWriter): bool
    {
        $outputWriter->write('Executing: ' . $cmd, OutputInterface::VERBOSITY_VERBOSE);
        $output = [];
        $exitCode = 0;
        exec($cmd, $output, $exitCode);
        $outputWriter->write($output, OutputInterface::VERBOSITY_VERBOSE);
        return $exitCode === 0;
    }

    protected function getCommandOutput($cmd, OutputInterface $outputWriter): array
    {
        $outputWriter->write('Executing: ' . $cmd, OutputInterface::VERBOSITY_VERBOSE);
        $output = [];
        $exitCode = 0;
        exec($cmd, $output, $exitCode);
        $outputWriter->write($output, OutputInterface::VERBOSITY_VERBOSE);
        return $output;
    }

    protected function confirm(
        $text,
        InputInterface $input,
        OutputInterface $output
    ) {
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion(
            $text,
            false
        );
        return $helper->ask($input, $output, $question);
    }

    protected function requestText(
        $text,
        InputInterface $input,
        OutputInterface $output
    ) {
        $helper = $this->getHelper('question');
        $question = new Question(
            $text,
            null
        );
        return $helper->ask($input, $output, $question);
    }
}
