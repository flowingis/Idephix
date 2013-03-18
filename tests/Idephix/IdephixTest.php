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

        $this->idx = new Idephix(new SSH\SshClient(new SSH\FakeSsh2Proxy($this)), array(),  $output);
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

    /**
     * @covers Ideato\Idephix::run
     * @todo   Implement testRun().
     */
    public function testRun()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
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
        $this->idx = new Idephix(new SSH\SshClient(new SSH\FakeSsh2Proxy($this)), array('test_target' => array()), $output);

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
        $this->idx = new Idephix(new SSH\SshClient(new SSH\FakeSsh2Proxy($this)), array('test_target' => array()), $output);
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
