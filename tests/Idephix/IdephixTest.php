<?php
namespace Idephix;

use Symfony\Component\Console\Output\StreamOutput;
use Idephix\Test\LibraryMock;

include_once(__DIR__."/PassTester.php");

class IdephixTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Idephix
     */
    protected $idx;

    protected function setUp()
    {
        $this->output = fopen("php://memory", 'r+');
        $output = new StreamOutput($this->output);

        $this->idx = new Idephix(array(), new SSH\SshClient(new SSH\FakeSsh2Proxy($this)), $output);
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
     */
    public function testAdd()
    {
        $this->idx->add('command_name', function () {
        });

        $this->assertTrue($this->idx->has('command_name'));
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
                array('env' => array('hosts' => array('localhost', '1.2.3.4'), 'ssh_params' => array('user' => 'test'))),
                "Local: echo \"Hello World from localhost\"\nHello World from localhost\n".
                "Local: echo \"Hello World from 1.2.3.4\"\nHello World from 1.2.3.4\n"
            ),
        );
    }

    /**
     * @dataProvider getArgvAndTargets
     */
    public function testRunALocalTask($argv, $target, $expected)
    {
        $_SERVER['argv'] = $argv;

        $output = fopen("php://memory", 'r+');
        $idx = new Idephix($target, new SSH\SshClient(new SSH\FakeSsh2Proxy($this)), new StreamOutput($output));
        $idx->getApplication()->setAutoExit(false);

        $idx->add('foo', function () use ($idx) {
            $idx->local('echo "Hello World from '.$idx->getCurrentTargetHost().'"');
        });

        $idx->run();

        rewind($output);

        $this->assertEquals($expected, stream_get_contents($output));
    }

    public function testRunLocalShouldAllowToDefineTimeout()
    {
        $_SERVER['argv'] = array('idx', 'foo');

        $output = fopen("php://memory", 'r+');
        $idx = new Idephix(array(), null, new StreamOutput($output));
        $idx->getApplication()->setAutoExit(false);

        $idx->add('foo', function () use ($idx) {
            $idx->local('sleep 2', false, 1);
        });

        $idx->run();

        rewind($output);

        $this->assertContains('ProcessTimedOutException', stream_get_contents($output));

    }

    /**
     */
    public function testAddLibrary()
    {
        $lib = new LibraryMock($this);
        $this->idx->addLibrary('name', $lib);
        $this->assertEquals(42, $this->idx->name()->test(42));
        $this->assertEquals(42, $this->idx->test(42));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The library must be an object
     */
    public function testAddLibraryNonObject()
    {
        $this->idx->addLibrary('name', 123);
    }

    /**
     */
    public function testRunTask()
    {
        $mock = $this->getMock("\\Idephix\\PassTester");
        $mock->expects($this->exactly(1))
            ->method('pass')
            ->with('foo');

        $this->idx->add('command_name', function ($param) use ($mock) {
            $mock->pass($param);

            return 0;
        });

        $this->assertEquals(0, $this->idx->runTask('command_name', 'foo'));
    }

    /**
     * Exception: Remote function need a valid environment. Specify --env parameter.
     */
    public function testRemote()
    {
        $output = new StreamOutput($this->output);
        $this->idx = new Idephix(array('test_target' => array()), new SSH\SshClient(new SSH\FakeSsh2Proxy($this)), $output);

        $this->idx->sshClient->setHost('host');
        $this->idx->sshClient->connect();
        $this->idx->remote('echo foo');
        rewind($this->output);
        $this->assertRegExp('/echo foo/m', stream_get_contents($this->output));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Remote function need a valid environment. Specify --env parameter.
     */
    public function testRemoteException()
    {
        $output = new StreamOutput($this->output);
        $this->idx = new Idephix(array('test_target' => array()), new SSH\SshClient(new SSH\FakeSsh2Proxy($this)), $output);
        $this->idx->remote('echo foo');
    }

    /**
     */
    public function testLocal()
    {
        $this->idx->local('echo foo');
        rewind($this->output);
        $this->assertRegExp('/echo foo/m', stream_get_contents($this->output));
    }

    public function getTaskAndReturnCode()
    {
        return array(
            array('fooOk', 0),
            array('fooKo', 1)
        );
    }

    /**
     * @dataProvider getTaskAndReturnCode
     */
    public function testReturnCode($task, $expected)
    {
        $_SERVER['argv'] = array('idx', $task);

        $output = fopen("php://memory", 'r+');
        $idx = new Idephix(array(), new SSH\SshClient(new SSH\FakeSsh2Proxy($this)), new StreamOutput($output));
        $idx->getApplication()->setAutoExit(false);

        $idx->add('fooOk', function () use ($idx) {
            $idx->local("echo 'God save the Queen'");
        });

        $idx->add('fooKo', function () use ($idx) {
            $idx->local("God save the Queen but this command will fail!");
        });

        $this->assertEquals($expected, $idx->run());
    }
}
