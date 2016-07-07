<?php
namespace Idephix\Console;

use Idephix\Task\ParametersDefinition;

class CommandBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_should_build_command()
    {
        $task = $this->prophesize('\Idephix\Task\Definition');
        $task->name()->willReturn('fooTask');
        $task->description()->willReturn('fooDescription');
        $task->parameters()->willReturn(ParametersDefinition::create(array(
            'bar' => array('description' => 'bar-description'),
            'foo' => array('defaultValue' => 'foo-value', 'description' => 'foo-description'),
            'go' => array('description' => 'dry run flag', 'defaultValue' => false)
        )));

        $command = CommandBuilder::fromTask($task->reveal());

        $this->assertInstanceOf('\Symfony\Component\Console\Command\Command', $command);
        $this->assertEquals('fooTask', $command->getName());
        $this->assertEquals('fooDescription', $command->getDescription());

        $this->assertEquals(2, $command->getDefinition()->getArgumentCount());
        $this->assertEquals(1, count($command->getDefinition()->getOptions()));

        $this->assertEquals('bar-description', $command->getDefinition()->getArgument('bar')->getDescription());
        $this->assertEquals('foo-description', $command->getDefinition()->getArgument('foo')->getDescription());
        $this->assertEquals('foo-value', $command->getDefinition()->getArgument('foo')->getDefault());
        $this->assertEquals('dry run flag', $command->getDefinition()->getOption('go')->getDescription());
        $this->assertFalse($command->getDefinition()->getOption('go')->getDefault());
    }
}
