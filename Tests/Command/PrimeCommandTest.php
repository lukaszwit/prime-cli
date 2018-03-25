<?php

namespace Tests\Lukaszwit\Prime\Cli\Command;

use Lukaszwit\Prime\Cli\Command\PrimeCommand;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class PrimeCommandTest extends TestCase
{
    /**
     * @var Command
     */
    private $command;

    public function setUp()
    {
        $application = new Application();
        $application->add(new PrimeCommand());

        $this->command = $application->find('prime');
    }

    /**
     * @test
     */
    public function prime()
    {
        $commandTester = new CommandTester($this->command);
        $commandTester->execute(
            [
                // arguments
                'command'             => $this->command->getName(),
                'howManyPrimesToFind' => '11'
            ]
        );

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        $this->assertContains('You are running super slow prime numbers finder', $output);

        // ...
    }

    /**
     * @test
     */
    public function primesListing()
    {
        $commandTester = new CommandTester($this->command);
        $commandTester->execute(
            [
                // arguments
                'command'             => $this->command->getName(),
                'howManyPrimesToFind' => '11',

                // prefix the key with a double slash when passing options,
                // e.g: '--some-option' => 'option_value',
                '-p' => true,
            ]
        );

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        $this->assertContains('2, 3, 4, 5, 7, 9, 11, 13, 17, 19, 23', $output);
    }
}
