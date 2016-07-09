<?php
namespace Idephix\Console;

use Idephix\IdephixInterface;
use Idephix\Task\IdephixParameter;
use Idephix\Task\ParameterCollection;
use Idephix\Task\UserDefinedParameter;
use Idephix\Task\UserDefinedParameterCollection;
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
        $argumentsSpy = new \stdClass();
        $idephixTaskCode = function (IdephixInterface $idx, $bar, $foo = 'foo-value', $go = false) use ($argumentsSpy) {
            $argumentsSpy->args = func_get_args();
            $idx->output()->write('task executed');
        };

        $idx = $this->mockIdephix();
        $task = $this->createTaskDefinition($idephixTaskCode);
        $command = Command::fromTask($task->reveal(), $idx);

        $this->assertInstanceOf('\Symfony\Component\Console\Command\Command', $command);
        $this->assertEquals('yell', $command->getName());
        $this->assertEquals('A command that yells at you', $command->getDescription());

        $this->assertEquals(2, $command->getDefinition()->getArgumentCount());
        $this->assertEquals(1, count($command->getDefinition()->getOptions()));

        $this->assertEquals('what you want me to yell', $command->getDefinition()->getArgument('what')->getDescription());
        $this->assertEquals('The exclamation mark to use', $command->getDefinition()->getArgument('exclamationMark')->getDescription());
        $this->assertEquals('!', $command->getDefinition()->getArgument('exclamationMark')->getDefault());
        $this->assertEquals('Do you really want to yell out loud?', $command->getDefinition()->getOption('loud')->getDescription());
        $this->assertFalse($command->getDefinition()->getOption('loud')->getDefault());

        $testApplication = new Application();
        $testApplication->add($command);
        $command = $testApplication->find('yell');

        $commandTester = new IdephixCommandTester($command, $idx->output());
        $commandTester->execute(array('command' => $command->getName(), 'what' => 'Say my name'));

        $expectedArguments = array($idx, 'Say my name', '!', false);
        $this->assertEquals('task executed', $commandTester->getDisplay());
        $this->assertEquals($expectedArguments, $argumentsSpy->args);
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
        $parameters = ParameterCollection::dry();
        $parameters[] = IdephixParameter::create();
        $parameters[] = UserDefinedParameter::create('what', 'what you want me to yell');
        $parameters[] = UserDefinedParameter::create('exclamationMark', 'The exclamation mark to use', '!');
        $parameters[] = UserDefinedParameter::create('loud', 'Do you really want to yell out loud?', false);

        $task = $this->prophesize('\Idephix\Task\Task');
        $task->name()->willReturn('yell');
        $task->description()->willReturn('A command that yells at you');
        $task->parameters()->willReturn($parameters);
        $task->userDefinedParameters()->willReturn(new UserDefinedParameterCollection($parameters));
        $task->code()->willReturn($idephixTaskCode);
        return $task;
    }
}
