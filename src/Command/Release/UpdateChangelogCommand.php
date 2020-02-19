<?php
namespace KayStrobach\Releasy\Command\Release;

use KayStrobach\Releasy\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateChangelogCommand extends AbstractCommand
{
    protected static $defaultName = 'release:updatechangelog';

    protected function configure()
    {
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Updates the changelog')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command uses the git history to update the changelog')
            ->addArgument('version', InputArgument::REQUIRED, 'version or tag name')
            ->addArgument('path', InputArgument::OPTIONAL, 'path', '.')
        ;
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    )
    {
        $content = PHP_EOL . '* '
            . implode(
                PHP_EOL . '* ',
                $this->getChangelogContent($output)
            );

        $changelogPath = $input->getArgument('path') . DIRECTORY_SEPARATOR . 'Changelog.md';

        if (!file_exists($changelogPath)) {
            touch($changelogPath);
        }

        file_put_contents(
            $changelogPath,
            implode(
                PHP_EOL,
                [
                    '# Version ' . $input->getArgument('version'),
                    $content,
                    '',
                    file_get_contents($changelogPath),
                ]
            )
        );

        $output->writeln('<info>✅  Updating the changelog in ' . $changelogPath . '</info>');
    }

    protected function getChangelogContent(OutputInterface $output)
    {
        $lines = $this->getCommandOutput(
            'git log $(git describe --tags --abbrev=0)..HEAD --pretty=format:"%h %s"',
            $output
        );
        if (count($lines) === 0) {
            $output->writeln('<info>❌  Did not found previous tag in git history, falling back to complete history.</info>');
            $lines = $this->getCommandOutput(
                'git log --first-parent --pretty=format:"%h %s"',
                $output
            );
        }
        return $lines;
    }
}