<?php
namespace Idephix;

use Idephix\Exception\FailedCommandException;
use Idephix\Task\Parameter;
use Idephix\Task\CallableTask;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Tests\Fixtures\DummyOutput;

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
     * @test
     * @expectedException  \Idephix\Exception\DeprecatedException
     */
    public function it_should_warn_if_not_using_correct_config_object()
    {
        $idx = new Idephix(array(), new DummyOutput(), new ArrayInput(array()));
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
    public function it_should_add_closure()
    {
        $this->idx->add(
            'commandName',
            function () {
            }
        );

        $this->assertTrue($this->idx->has('commandName'));
    }

    /**
     * @test
     */
    public function it_should_add_task()
    {
        $task = new CallableTask('fooCommand', 'A dummy command', function () {}, Parameter\Collection::dry());
        $this->idx->add($task);

        $this->assertTrue($this->idx->has('fooCommand'));

        $registeredCommands = $this->idx->getApplication()->all();
        $this->assertArrayHasKey('fooCommand', $registeredCommands);
        $this->assertInstanceOf('\Idephix\Console\Command', $registeredCommands['fooCommand']);
        $this->assertEquals('fooCommand', $registeredCommands['fooCommand']->getName());
    }

    /**
     * @test
     * @expectedException \Idephix\Exception\InvalidTaskException
     */
    public function it_should_throw_exception_for_missing_code()
    {
        $this->idx->add('command_name');
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
            ), new StreamOutput($output)
        );
        $idx->getApplication()->setAutoExit(false);

        $idx->add(
            'foo',
            function () use ($idx) {
                $idx->local('echo "Hello World from ' . $idx->getCurrentTargetHost() . '"');
            }
        );

        $idx->run();

        rewind($output);

        $this->assertEquals($expected, stream_get_contents($output));
    }

    public function testRunLocalShouldAllowToDefineTimeout()
    {
        $_SERVER['argv'] = array('idx', 'foo');

        $output = fopen('php://memory', 'r+');
        $idx = new Idephix(Config::dry(), new StreamOutput($output));
        $idx->getApplication()->setAutoExit(false);

        $idx->add(
            'foo',
            function () use ($idx) {
                $idx->local('sleep 2', false, 1);
            }
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
        $this->idx->add('test', function (IdephixInterface $idx, $what) {return $what * 2;});
        $this->assertEquals(84, $this->idx->runTask('test', 42));
        $this->assertEquals(84, $this->idx->test(42));
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
