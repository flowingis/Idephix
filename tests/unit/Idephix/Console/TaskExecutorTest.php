<?php
namespace Idephix\Console;

use Idephix\Exception\FailedCommandException;
use Idephix\SSH\SshClient;
use Idephix\Task\TaskCollection;
use Idephix\Task\Parameter;
use Idephix\Task\CallableTask;
use Idephix\Test\SSH\StubProxy;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Input\StringInput;
use Idephix\Console\Application;
use Idephix\Context;

class TaskExecutorTest extends \PHPUnit_Framework_TestCase
{
    protected $context;

    protected $executor;

    protected function setUp()
    {
        $output = new StreamOutput(fopen('php://memory', 'r+'));
        $input = new StringInput('');

        $this->context = $this->prophesize('\Idephix\Context');
        $this->executor = new Application(
            null,
            null,
            null,
            $input,
            $output
        );
    }

    /**
     * @test
     */
    public function it_should_be_able_to_add_task_from_closure()
    {
        $task = CallableTask::buildFromClosure('commandName', function () {});

        $this->executor->addTask($task, $this->context->reveal());

        $this->assertTrue($this->executor->has('commandName'));
    }

    /**
     * @test
     */
    public function it_should_be_able_to_add_task_from_object()
    {
        $task = new CallableTask('fooCommand', 'A dummy command', function () {}, Parameter\Collection::dry());

        $this->executor->addTask($task, $this->context->reveal());

        $this->assertTrue($this->executor->has('fooCommand'));

        $registeredCommands = $this->executor->all();

        $this->assertArrayHasKey('fooCommand', $registeredCommands);
        $this->assertInstanceOf('\Idephix\Console\Command', $registeredCommands['fooCommand']);
        $this->assertEquals('fooCommand', $registeredCommands['fooCommand']->getName());
    }

    /**
     * @test
     */
    public function it_should_allow_to_invoke_tasks()
    {
        $task = CallableTask::buildFromClosure(
                'test',
                function (Context $idx, $what, $go = false) {
                    if ($go) {
                        return $what * 2;
                    }
                    return 0;
                }
        );

        $this->executor->addTask($task, $this->context->reveal());

        $this->assertEquals(84, $this->executor->runTask('test', array(42, true)));
        $this->assertEquals(0, $this->executor->runTask('test', array(42)));
    }

    /**
     * @test
     */
    public function it_should_inject_context()
    {
        $task = CallableTask::buildFromClosure(
                'foo',
                function (Context $ctx) {
                    $ctx->local('sleep 2', false);
                }
        );

        $this->context->local('sleep 2', false)->shouldBeCalled();

        $this->executor->addTask($task, $this->context->reveal());

        $this->executor->runTask('foo', array());
    }
}