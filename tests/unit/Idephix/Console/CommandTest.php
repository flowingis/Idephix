<?php
namespace Idephix\Console;

use Idephix\Context;
use Idephix\Task\Parameter;
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
        $idephixTaskCode = function (Context $idx, $bar, $foo = 'foo-value', $go = false) use ($argumentsSpy) {
            $argumentsSpy->args = func_get_args();
            $idx->output()->write('task executed');
        };

        $context = $this->mockContext();
        $task = $this->createTaskDefinition($idephixTaskCode);
        $command = Command::fromTask($task->reveal(), $this->mockIdephix($context));

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

        $commandTester = new IdephixCommandTester($command, $context->output());
        $commandTester->execute(array('command' => $command->getName(), 'what' => 'Say my name'));

        $expectedArguments = array($context, 'Say my name', '!', false);
        $this->assertEquals('task executed', $commandTester->getDisplay());
        $this->assertEquals($expectedArguments, $argumentsSpy->args);
    }

    /**
     * @return object|\Prophecy\Prophecy\ObjectProphecy
     */
    private function mockContext()
    {
        $context = $this->prophesize('\Idephix\Context');
        $context->output()->willReturn(new StreamOutput(fopen('php://memory', 'r+')));
        $context = $context->reveal();

        return $context;
    }

    /**
     * @param $idephixTaskCode
     * @return \Prophecy\Prophecy\ObjectProphecy
     */
    private function createTaskDefinition($idephixTaskCode)
    {
        $parameters = Parameter\Collection::dry();
        $parameters[] = Parameter\Context::create();
        $parameters[] = Parameter\UserDefined::create('what', 'what you want me to yell');
        $parameters[] = Parameter\UserDefined::create('exclamationMark', 'The exclamation mark to use', '!');
        $parameters[] = Parameter\UserDefined::create('loud', 'Do you really want to yell out loud?', false);

        $task = $this->prophesize('\Idephix\Task\CallableTask');
        $task->name()->willReturn('yell');
        $task->description()->willReturn('A command that yells at you');
        $task->parameters()->willReturn($parameters);
        $task->userDefinedParameters()->willReturn(new Parameter\UserDefinedCollection($parameters));
        $task->code()->willReturn($idephixTaskCode);
        return $task;
    }

    private function mockIdephix($context)
    {
        $idx = $this->prophesize('\Idephix\Idephix');
        $idx->getContext()->willReturn($context);

        return $idx->reveal();
    }
}
