<?php
namespace Idephix;

use Symfony\Component\Console\Output\StreamOutput;
use Idephix\Test\LibraryMock;

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
     * @covers Ideato\Idephix::__get
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
     * @covers Ideato\Idephix::add
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
            array(array('idx', 'foo'), array()),
            array(array('idx', 'foo', '--env=env'), array('env' => array('hosts' => array('localhost'), 'ssh_params' => array('user' => 'test')))),
        );
    }

    /**
     * @covers Ideato\Idephix::run
     * @dataProvider getArgvAndTargets
     */
    public function testRunALocalTask($argv, $target)
    {
        $_SERVER['argv'] = $argv;

        $output = fopen("php://memory", 'r+');
        $idx = new Idephix($target, new SSH\SshClient(new SSH\FakeSsh2Proxy($this)), new StreamOutput($output));
        $idx->getApplication()->setAutoExit(false);

        $idx->add('foo', function() use ($idx){
            $idx->local('echo "Hello World"');
        });

        $idx->run();

        rewind($output);

        $expected = "Local: echo \"Hello World\"\nHello World\n";
        $this->assertEquals($expected, stream_get_contents($output));
    }

    /**
     * @covers Ideato\Idephix::addLibrary
     * @covers Ideato\Idephix::__call
     */
    public function testAddLibrary()
    {
        $lib = new LibraryMock($this);
        $this->idx->addLibrary('name', $lib);
        $this->assertEquals(42, $this->idx->name()->test(42));
        $this->assertEquals(42, $this->idx->test(42));
    }

    /**
     * @covers Ideato\Idephix::addLibrary
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The library must be an object
     */
    public function testAddLibraryNonObject()
    {
        $this->idx->addLibrary('name', 123);
    }

    /**
     * @covers Ideato\Idephix::runTask
     */
    public function testRunTask()
    {
        $this->idx->add('command_name', function ($param) {
            return $param;
        });

        $this->assertEquals('foo', $this->idx->runTask('command_name', 'foo'));
    }

    /**
     * @covers Ideato\Idephix::remote
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
     * @covers Ideato\Idephix::remote
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
     * @covers Ideato\Idephix::local
     */
    public function testLocal()
    {
        $this->idx->local('echo foo');
        rewind($this->output);
        $this->assertRegExp('/echo foo/m', stream_get_contents($this->output));
    }
}
