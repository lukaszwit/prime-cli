<?php

namespace Lukaszwit\Prime\Cli\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Stopwatch\StopwatchEvent;

class PrimeCommand extends Command
{
    private $stopwatch;

    private $howManyPrimesToFind;

    private $showSummary = true;

    private $showPrimes = false;

    private $optimize;

    private $exitStatus = null;

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('prime')
            // the short description shown while running "php bin/console list"
            ->setDescription('Prime computer.')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp("This command allows you to test what symfony command can do")
            // configure an argument
            ->addArgument('howManyPrimesToFind', InputArgument::OPTIONAL, 'I need your name to proceed.')
            ->addOption('summary', 's', InputOption::VALUE_NONE, 'Do you want to see summary after command finished?')
            ->addOption(
                'showPrimes',
                'p',
                InputOption::VALUE_NONE,
                'Do you want to see all prime numbers after command finished?'
            )
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Do you want to continue even if operation might me time consuming?'
            )
            ->addOption('optimize', 'o', InputOption::VALUE_OPTIONAL, 'Do you want to use square optimization? [y/n]', 'y');
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getHelper('question');

        $this->howManyPrimesToFind = $input->getArgument('howManyPrimesToFind');
        if (!$this->howManyPrimesToFind) {
            $question = new Question('Please enter the number of primes you wish to find: ', 2);
            $this->howManyPrimesToFind = $questionHelper->ask($input, $output, $question);
        }

        if ($this->howManyPrimesToFind < 2) {
            $formatter = $this->getHelper('formatter');
            $errorMessages = array('Error!', 'You need to provide positive number greater than 1');
            $formattedBlock = $formatter->formatBlock($errorMessages, 'error', true);
            $output->writeln($formattedBlock);

            $this->exitStatus = 1;
            return;
        }

        $force = $input->getOption('force');
        if (!$force && $this->howManyPrimesToFind > 1000) {
            $question = new ConfirmationQuestion('This operation might take a while. Do you wish to continue? ', false);
            if (!$questionHelper->ask($input, $output, $question)) {
                $this->exitStatus = 0;
                return;
            }
        }

        $this->showSummary = $input->getOption('summary');
        $this->optimize = $input->getOption('optimize');
        $this->showPrimes = $input->getOption('showPrimes');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // returning if exit code was set before
        if (null !== $this->exitStatus) {
            return (int) $this->exitStatus;
        }

        $formatter = $this->getHelper('formatter');

        $formattedBlock = $formatter->formatSection('Welcome', 'You are running super slow prime numbers finder', 'info');
        $output->writeln($formattedBlock);

        $this->stopwatch = new Stopwatch();
        $this->stopwatch->start('prime');
        $foundPrimes = $this->findNprimes($this->howManyPrimesToFind, $output);
        $output->writeln('');
        $event = $this->stopwatch->stop('prime');

        if ($this->showPrimes) {
            $this->showPrimes($foundPrimes, $output);
        }

        if ($this->showSummary) {
            $this->showSummary($foundPrimes, $event, $output);
        }

        $output->writeln('All operation are done. This is the end!');
    }

    private function showPrimes(array $foundPrimes, OutputInterface $output)
    {
        $formatter = $this->getHelper('formatter');
        $formattedLine = $formatter->formatSection(
            'Found primes',
            implode(', ', $foundPrimes)
        );

        $output->writeln($formattedLine);
    }

    private function showSummary(array $foundPrimes, StopwatchEvent $event, OutputInterface $output)
    {
        $table = new Table($output);
        $table
            ->setHeaders(['Prime numbers found', 'Total computing time']);
        $table->addRow([count($foundPrimes), $event->getDuration() . ' ms']);

        $table->render();
    }

    private function findNprimes($howManyPrimesToFind, $output)
    {
        $progress = new ProgressBar($output, $howManyPrimesToFind);
        $progress->start();

        $startNumber = 2;
        $currentNumber = $startNumber;
        $foundNumbersCount = 0;
        $foundNumbers = [];
        $howManyPrimesToFind = (int)$howManyPrimesToFind;

        while ($foundNumbersCount < $howManyPrimesToFind) {
            if ($this->isPrime($currentNumber)) {
                ++$foundNumbersCount;
                $foundNumbers[] = $currentNumber;
                $progress->advance();
                $this->stopwatch->lap('prime');
            }

            ++$currentNumber;
        }

        $progress->finish();

        return $foundNumbers;
    }

    private function isPrime($numberToCheck)
    {
        $iterationsCount = $numberToCheck;
        if ('y' === $this->optimize) {
            $iterationsCount = sqrt($numberToCheck);
        }

        if ($numberToCheck < 2) {
            return false;
        } else {
            for ($i = 2; $i < $iterationsCount; $i++) {
                if (($numberToCheck % $i) === 0) {
                    return false;
                }
            }
        }

        return true;
    }
}
