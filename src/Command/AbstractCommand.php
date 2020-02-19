<?php

declare(strict_types=1);

namespace KayStrobach\Releasy\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

abstract class AbstractCommand extends Command
{
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
            $text . ' <info>no</info> ',
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

    protected function callInternalCommand(
        string $commandName,
        array $arguments,
        OutputInterface $output
    ) {
        $command = $this->getApplication()->find($commandName);
        $input = new ArrayInput($arguments);
        $code = $command->run($input, $output);
        if ($code !== 0) {
            throw new \Exception($commandName . ' failed to execute');
        }
    }
}