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
        $tasks = TaskCollection::dry();

        $idx = new Idephix(
            Config::fromArray(
                array(\Idephix\Config::ENVS => $targets, 'ssh_client' => $sshClient)
            ),
            $tasks,
            new StreamOutput($output)
        );

        // $idx->getApplication()->setAutoExit(false);

        $idx->addTask(
            CallableTask::buildFromClosure(
                'foo',
                function (Context $ctx) {
                    $ctx->local('echo "Hello World from ' . $ctx->getCurrentHost() . '"');
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
            TaskCollection::dry(),
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
