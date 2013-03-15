<?php
namespace Idephix;

class IdephixTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Idephix
     */
    protected $object;

    protected function setUp()
    {
        $this->object = new Idephix(new SSH\SshClient(new SSH\FakeSsh2Proxy($this)));
    }

    /**
     * @covers Ideato\Idephix::__call
     * @todo   Implement test__call().
     */
    public function test__call()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Ideato\Idephix::__get
     * @expectedException PHPUnit_Framework_Error
     * @expectedExceptionMessage Undefined property: application in
     */
    public function test__get()
    {
        $this->assertInstanceOf('\Ideato\SSH\SshClient', $this->object->sshClient);
        $this->assertInstanceOf('\Symfony\Component\Console\Output\OutputInterface', $this->object->output);

        $this->object->application;
    }

    /**
     * @covers Ideato\Idephix::add
     * @todo   Implement testAdd().
     */
    public function testAdd()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Ideato\Idephix::getCurrentTarget
     * @todo   Implement testGetCurrentTarget().
     */
    public function testGetCurrentTarget()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Ideato\Idephix::getCurrentTargetName
     * @todo   Implement testGetCurrentTargetName().
     */
    public function testGetCurrentTargetName()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
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
     * @todo   Implement testAddLibrary().
     */
    public function testAddLibrary()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Ideato\Idephix::runTask
     * @todo   Implement testRunTask().
     */
    public function testRunTask()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Ideato\Idephix::remote
     * @todo   Implement testRemote().
     */
    public function testRemote()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Ideato\Idephix::local
     * @todo   Implement testLocal().
     */
    public function testLocal()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }
}
