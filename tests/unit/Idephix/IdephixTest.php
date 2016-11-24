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

class IdephixTest extends \PHPUnit_Framework_TestCase
{
    public function getArgvAndTargets()
    {
        return array(
            array(
                "bar",
                array(),
                "Local: echo \"Hello World from bar task\"\nHello World from bar task\n"
            ),
            array(
                "foo --env=env",
                array('env' => array(
                    'hosts' => array('localhost'),
                    'ssh_params' => array('user' => 'test'))
                ),
                "Local: echo \"Hello World from localhost\"\nHello World from localhost\n"
            ),
            array(
                "foo --env=env",
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
     * @test
     */
    public function it_should_run_a_task_on_every_host($args, $targets, $expected)
    {
        $conf = array(
            \Idephix\Config::ENVS => $targets,
            'ssh_client' => new SSH\SshClient(new Test\SSH\StubProxy())
        );

        $output = new BufferedOutput(fopen('php://memory', 'r+'));

        $idx = new Idephix(
            Config::fromArray($conf),
            TaskCollection::dry(),
            $output,
            new StringInput($args)
        );

        $idx->addTask(
            CallableTask::buildFromClosure(
                'foo',
                function (Context $ctx) {
                    $ctx->local("echo \"Hello World from {$ctx->getCurrentHost()}\"");
                }
            )
        );

        $idx->addTask(
            CallableTask::buildFromClosure(
                'bar',
                function (Context $ctx) {
                    $ctx->local("echo \"Hello World from bar task\"");
                }
            )
        );

        $idx->run();

        $this->assertEquals($expected, $output->fetch());
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