<?php

namespace Tests\Lukaszwit\Prime\Cli\Command;

use Lukaszwit\Prime\Cli\Command\PrimeCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class PrimeCommandTest extends KernelTestCase
{
    public function testPrime()
    {
        self::bootKernel();
        $application = new Application(self::$kernel);

        $application->add(new PrimeCommand());

        $command = $application->find('prime');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command'  => $command->getName(),
            'howManyPrimesToFind' => '11',

            // prefix the key with a double slash when passing options,
            // e.g: '--some-option' => 'option_value',
        ));

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        $this->assertContains('You are running super slow prime numbers finder', $output);

        // ...
    }
}
