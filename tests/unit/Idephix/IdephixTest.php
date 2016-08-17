<?php
namespace Idephix;

use Idephix\Exception\FailedCommandException;
use Idephix\SSH\SshClient;
use Idephix\Task\Parameter;
use Idephix\Task\CallableTask;
use Idephix\Test\SSH\StubProxy;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\StreamOutput;

class IdephixTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Idephix
     */
    protected $idx;

    protected $output;

    protected function setUp()
    {
        $this->output = fopen('php://memory', 'r+');
        $output = new StreamOutput($this->output);

        $this->idx = new Idephix(
            Config::fromArray(
                array('targets' => array(), 'sshClient' => new SSH\SshClient(new Test\SSH\StubProxy()))
            ), $output
        );
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     * @expectedExceptionMessage Undefined property: application in
     */
    public function test__get()
    {
        $this->assertInstanceOf('\Idephix\SSH\SshClient', $this->idx->sshClient);
        $this->assertInstanceOf('\Symfony\Component\Console\Output\OutputInterface', $this->idx->output);

        $this->idx->application;
    }

    /**
     * @test
     */
    public function it_should_be_able_to_add_task_from_closure()
    {
        $this->idx->addTask(
            CallableTask::buildFromClosure(
                'commandName',
                function () {
                }
            )
        );

        $this->assertTrue($this->idx->has('commandName'));
    }

    /**
     * @test
     */
    public function it_should_be_able_to_add_task_from_object()
    {
        $task = new CallableTask('fooCommand', 'A dummy command', function () {}, Parameter\Collection::dry());
        $this->idx->addTask($task);

        $this->assertTrue($this->idx->has('fooCommand'));

        $registeredCommands = $this->idx->getApplication()->all();
        $this->assertArrayHasKey('fooCommand', $registeredCommands);
        $this->assertInstanceOf('\Idephix\Console\Command', $registeredCommands['fooCommand']);
        $this->assertEquals('fooCommand', $registeredCommands['fooCommand']->getName());
    }
    
    public function getArgvAndTargets()
    {
        return array(
            array(
                array('idx', 'foo'),
                array(),
                "Local: echo \"Hello World from \"\nHello World from \n"
            ),
            array(
                array('idx', 'foo', '--env=env'),
                array('env' => array('hosts' => array('localhost'), 'ssh_params' => array('user' => 'test'))),
                "Local: echo \"Hello World from localhost\"\nHello World from localhost\n"
            ),
            array(
                array('idx', 'foo', '--env=env'),
                array(
                    'env' => array(
                        'hosts' => array('localhost', '1.2.3.4'),
                        'ssh_params' => array('user' => 'test')
                    )
                ),
                "Local: echo \"Hello World from localhost\"\nHello World from localhost\n" .
                "Local: echo \"Hello World from 1.2.3.4\"\nHello World from 1.2.3.4\n"
            ),
        );
    }

    /**
     * @dataProvider getArgvAndTargets
     */
    public function testRunALocalTask($argv, $targets, $expected)
    {
        $_SERVER['argv'] = $argv;

        $sshClient = new SSH\SshClient(
            new Test\SSH\StubProxy()
        );
        $output = fopen('php://memory', 'r+');
        $idx = new Idephix(
            Config::fromArray(
                array('targets' => $targets, 'sshClient' => $sshClient)
            ),
            new StreamOutput($output)
        );
        $idx->getApplication()->setAutoExit(false);

        $idx->addTask(
            CallableTask::buildFromClosure(
                'foo',
                function (Context $idx) {
                    $idx->local('echo "Hello World from ' . $idx['target.host'] . '"');
                }
            )
        );

        $idx->run();

        rewind($output);

        $this->assertEquals($expected, stream_get_contents($output));
    }

    /** @test */
    public function it_should_inject_context_to_task()
    {
        $_SERVER['argv'] = array('idx', 'myTask', '--env=prod');

        $idx = new Idephix(
            Config::fromArray(array(
                Config::SSHCLIENT => new SshClient(new StubProxy()),
                Config::TARGETS => array(
                'prod' => array(
                    'hosts' => array('127.0.0.1'),
                    'ssh_params' => array('user' => 'ftassi'),
                    'foo' => 'bar'
                )
            ))),
            new StreamOutput(fopen('php://memory', 'r+'))
        );

        $spy = new \stdClass();
        $idx->addTask(
            CallableTask::buildFromClosure(
                'myTask',
                function (Context $context) use ($spy) {
                    $spy->args = func_get_args();
                }
            )
        );

        $idx->run();

        /** @var Context $context */
        $context = $spy->args[0];

        $this->assertInstanceOf('\Idephix\Context', $context);
        $this->assertEquals('bar', $context['foo']);
        $this->assertEquals('prod', $context['target.name']);
        $this->assertEquals('127.0.0.1', $context['target.host']);
    }

    public function testRunLocalShouldAllowToDefineTimeout()
    {
        $_SERVER['argv'] = array('idx', 'foo');

        $output = fopen('php://memory', 'r+');
        $idx = new Idephix(Config::dry(), new StreamOutput($output));
        $idx->getApplication()->setAutoExit(false);

        $idx->addTask(
            CallableTask::buildFromClosure(
                'foo',
                function (Context $idx) {
                    $idx->local('sleep 2', false, 1);
                }
            )
        );

        try {
            $idx->run();
        } catch (FailedCommandException $e) {
            // do nothing, is expected to fail
        }

        rewind($output);

        $this->assertContains('ProcessTimedOutException', stream_get_contents($output));
    }

    /**
     * @test
     */
    public function it_should_allow_to_invoke_tasks()
    {
        $this->idx->addTask(
            CallableTask::buildFromClosure(
                'test',
                function (Context $idx, $what, $go = false) {
                    if ($go) {
                        return $what * 2;
                    }
                    return 0;
                }
            )
        );
        $this->assertEquals(84, $this->idx->test(42, true));
        $this->assertEquals(84, $this->idx->runTask('test', 42, true));
    }

    /**
     * Exception: Remote function need a valid environment. Specify --env parameter.
     */
    public function testRemote()
    {
        $output = new BufferedOutput($this->output);
        $sshClient = new SSH\SshClient(new Test\SSH\StubProxy('Remote output from '));
        $this->idx = new Idephix(
            Config::fromArray(array('targets' => array('test_target' => array()), 'sshClient' => $sshClient)),
            $output
        );

        $this->idx->sshClient->setHost('host');
        $this->idx->sshClient->connect();
        $this->idx->remote('echo foo');

        $rows = explode("\n", $output->fetch());
        $this->assertCount(3, $rows);
        $this->assertEquals('Remote: echo foo', $rows[0]);
        $this->assertEquals('Remote output from echo foo', $rows[1]);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Remote function need a valid environment. Specify --env parameter.
     */
    public function testRemoteException()
    {
        $output = new StreamOutput($this->output);
        $sshClient = new SSH\SshClient(new Test\SSH\StubProxy());
        $this->idx = new Idephix(
            Config::fromArray(array('targets' => array('test_target' => array()), 'sshClient' => $sshClient)),
            $output
        );
        $this->idx->remote('echo foo');
    }

    public function testLocal()
    {
        $this->idx->local('echo foo');
        rewind($this->output);
        $this->assertRegExp('/echo foo/m', stream_get_contents($this->output));
    }
}

class TaskSpy
{
    public $executed = false;
    public $lastCallArguments = array();

    public function execute($myParam)
    {
        $this->executed = true;
        $this->lastCallArguments = func_get_args();
    }
}
