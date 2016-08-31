<?php
namespace Idephix;

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
        $input = new StringInput('');

        $config = Config::fromArray(array(
            'envs' => array(),
            'ssh_client' => new SSH\SshClient(new Test\SSH\StubProxy()))
        );
        $tasks = TaskCollection::dry();

        $this->idx = new Idephix($config, $tasks, $output);
    }

    /**
     * @test
     *
     * @todo  move to TaskExecutor test
     */
    public function it_should_allow_to_define_custom_timeout()
    {
        $task = CallableTask::buildFromClosure(
                'foo',
                function (Context $ctx) {
                    $ctx->local('sleep 2', false, 1);
                }
        );

        $this->context->local('sleep 2', false, 1)->shouldBeCalled();

        $this->executor->addTask($task, $this->context->reveal());


        try {
            $this->executor->runTask('foo', array());
        } catch (FailedCommandException $e) {

        }

        $this->fail();
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
                array(\Idephix\Config::ENVS => $targets, 'ssh_client' => $sshClient)
            ),
            new StreamOutput($output)
        );
        $idx->getApplication()->setAutoExit(false);

        $idx->addTask(
            CallableTask::buildFromClosure(
                'foo',
                function (Context $idx) {
                    $idx->local('echo "Hello World from ' . $idx['env.host'] . '"');
                }
            )
        );

        $idx->run();

        rewind($output);

        $this->assertEquals($expected, stream_get_contents($output));
    }

    /**
     * @test
     */
    public function it_should_print_user_tasks_separately()
    {
        $output = fopen('php://memory', 'r+');
        $idx = new Idephix(
            Config::dry(),
            new StreamOutput($output),
            new StringInput('')
        );

        $idx->addTask(CallableTask::buildFromClosure('custom1', function () {}));
        $idx->addTask(CallableTask::buildFromClosure('custom2', function () {}));

        $idx->run();

        rewind($output);

        $out = stream_get_contents($output);

        $this->assertContains('Available commands', $out);
        $this->assertContains('User tasks', $out);
    }


    /**
     * Exception: Remote function need a valid environment. Specify --env parameter.
     *
     *  @todo  move to operations test
     *
     */
    public function testRemote()
    {
        $output = new BufferedOutput($this->output);
        $sshClient = new SSH\SshClient(new Test\SSH\StubProxy('Remote output from '));
        $this->idx = new Idephix(
            Config::fromArray(array('envs' => array('test_target' => array()), 'ssh_client' => $sshClient)),
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
     *
     * @todo  move to operations test
     */
    public function testRemoteException()
    {
        $output = new StreamOutput($this->output);
        $sshClient = new SSH\SshClient(new Test\SSH\StubProxy());
        $this->idx = new Idephix(
            Config::fromArray(array('envs' => array('test_target' => array()), 'ssh_client' => $sshClient)),
            $output
        );
        $this->idx->remote('echo foo');
    }

    /**
     * @todo move to operation test
     */
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
