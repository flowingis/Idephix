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

        $taskCode = function (Context $ctx, $bar, $foo = 'foo-value', $go = false) use ($argumentsSpy) {

            $argumentsSpy->args = func_get_args();

            $ctx->output()->write('task executed');
        };

        $context = $this->mockContext();
        $task = $this->createTaskDefinition($taskCode);

        $command = Command::fromTask($task->reveal(), $context);

        $def = $command->getDefinition();

        $this->assertInstanceOf('\Symfony\Component\Console\Command\Command', $command);
        $this->assertEquals('yell', $command->getName());
        $this->assertEquals('A command that yells at you', $command->getDescription());

        $this->assertEquals(2, $def->getArgumentCount());
        $this->assertEquals(1, count($def->getOptions()));

        $this->assertEquals('what you want me to yell', $def->getArgument('what')->getDescription());
        $this->assertEquals('The exclamation mark to use', $def->getArgument('exclamationMark')->getDescription());
        $this->assertEquals('!', $def->getArgument('exclamationMark')->getDefault());
        $this->assertEquals('Do you really want to yell out loud?', $def->getOption('loud')->getDescription());
        $this->assertFalse($def->getOption('loud')->getDefault());


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
}
