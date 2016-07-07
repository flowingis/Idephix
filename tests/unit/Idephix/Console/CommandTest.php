<?php
namespace Idephix\Console;

use Idephix\IdephixInterface;
use Idephix\Task\ParameterCollection;
use Idephix\Test\Console\IdephixCommandTester;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\StreamOutput;

class CommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_should_build_command()
    {
        $idephixTaskCode = function (IdephixInterface $idx, $bar, $foo = 'foo-value', $go = false) {
            $idx->output()->write('task executed');
        };

        $idx = $this->mockIdephix();
        $task = $this->createTaskDefinition($idephixTaskCode);
        $command = Command::fromTask($task->reveal(), $idx);

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

        $testApplication = new Application();
        $testApplication->add($command);
        $command = $testApplication->find('fooTask');

        $commandTester = new IdephixCommandTester($command, $idx->output());
        $commandTester->execute(array('command' => $command->getName(), 'bar' => 'foobar'));

        $this->assertEquals('task executed', $commandTester->getDisplay());
    }

    /**
     * @return object|\Prophecy\Prophecy\ObjectProphecy
     */
    private function mockIdephix()
    {
        $idx = $this->prophesize('\Idephix\IdephixInterface');
        $idx->output()->willReturn(new StreamOutput(fopen('php://memory', 'r+')));
        $idx = $idx->reveal();
        return $idx;
    }

    /**
     * @param $idephixTaskCode
     * @return \Prophecy\Prophecy\ObjectProphecy
     */
    private function createTaskDefinition($idephixTaskCode)
    {
        $task = $this->prophesize('\Idephix\Task\Task');
        $task->name()->willReturn('fooTask');
        $task->description()->willReturn('fooDescription');
        $task->parameters()->willReturn(
            ParameterCollection::create(
                array(
                    'bar' => array('description' => 'bar-description'),
                    'foo' => array('defaultValue' => 'foo-value', 'description' => 'foo-description'),
                    'go' => array('description' => 'dry run flag', 'defaultValue' => false)
                )
            )
        );
        $task->code()->willReturn($idephixTaskCode);
        return $task;
    }
}
