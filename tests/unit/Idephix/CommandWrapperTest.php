<?php
namespace Idephix;

use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Idephix\CommandWrapper;

class CommandWrapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests not enough arguments passed to command
     *
     * @expectedException RuntimeException
     * @expectedExceptionMessage Not enough arguments.
     */
    public function testParametersNotEnoughArguments()
    {
        $cw = $this->buildCommandWrapper();

        $appIndexDefinition = $this->buildInputDefinition();
        $this->mergeInputDefinitions($cw, $appIndexDefinition);

        $input = new ArgvInput(array('cmd'));
        $input->bind($cw->getDefinition());

        $input = $cw->filterByOriginalDefinition($input, $appIndexDefinition);
        $input->validate();
    }

    public function testParameters()
    {
        $cw = $this->buildCommandWrapper();
        $originaDefinition = clone $cw->getDefinition();

        $appIndexDefinition = $this->buildInputDefinition();
        $this->mergeInputDefinitions($cw, $appIndexDefinition);

        $input = new ArgvInput(array('cmd', 'uno_value', 'name_value', '--go'));
        $input->bind($cw->getDefinition());

        $input = $cw->filterByOriginalDefinition($input, $appIndexDefinition);
        $input->validate();

        $this->assertEquals(
            count($originaDefinition->getArguments()),
            count($input->getArguments())
        );

        $this->assertEquals(1, count($input->getArguments()));
        $this->assertEquals(array('name' => 'name_value'), $input->getArguments());
        $this->assertEquals(array('go' => true), $input->getOptions());
    }

    private function buildInputDefinition()
    {
        $inputDefinition = new InputDefinition();
        $inputDefinition->addArgument(
            new InputArgument('uno', InputArgument::REQUIRED)
        );
        $inputDefinition->addOption(
            new InputOption('due', null, InputOption::VALUE_NONE)
        );

        return $inputDefinition;
    }

    private function mergeInputDefinitions($cw, $inputDefinition)
    {
        $cw->getDefinition()->setArguments(array_merge(
            $inputDefinition->getArguments(),
            $cw->getDefinition()->getArguments()
        ));

        $cw->getDefinition()->addOptions($inputDefinition->getOptions());
    }

    private function buildCommandWrapper()
    {
        $cw = new CommandWrapper('pippo');
        /**
         * Esegue il touch di un file specificato in input
         * @param string $name il nome del file
         * @param bool   $go   se specificato esegue il comando, altrimenti dry-run
         */
        $cw->buildFromCode(function ($name, $go = false) {
            // do nothing
        });

        return $cw;
    }
}
