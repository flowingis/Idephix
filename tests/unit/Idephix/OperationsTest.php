<?php
namespace Idephix;

use Idephix\Operations;
use Idephix\SSH\SshClient;
use Idephix\Test\SSH\StubProxy;
use Symfony\Component\Console\Output\BufferedOutput;
use Idephix\Extension\CallableMethod;
use Idephix\Extension\MethodCollection;

class OperationsTest extends \PHPUnit_Framework_TestCase
{
    private $output;

    private $sshClient;

    private $operations;

    public function setUp()
    {
        $this->output = new BufferedOutput(fopen('php://memory', 'r+'));
        $this->sshClient = new SSH\SshClient(new Test\SSH\StubProxy('Remote output from '));

        $this->operations = new Operations($this->sshClient, $this->output);
    }

    /**
     * @test
     */
    public function it_should_run_local_commands()
    {
        $this->operations->local('echo foo');

        $rows = explode("\n", $this->output->fetch());

        $this->assertCount(3, $rows);
        $this->assertEquals('Local: echo foo', $rows[0]);
        $this->assertEquals('foo', $rows[1]);
    }

    /**
     * @expectedException Symfony\Component\Process\Exception\ProcessTimedOutException
     * @expectedExceptionMessage The process "sleep 3" exceeded the timeout of 1 seconds.
     *
     * @test
     */
    public function it_should_allow_to_define_custom_timeout()
    {
        $this->operations->local('sleep 3', false, 1);
    }

    /**
     * @test
     */
    public function it_should_run_remote_commands()
    {
        $this->sshClient->setHost('host');
        $this->sshClient->connect();

        $this->operations->remote('echo foo');

        $rows = explode("\n", $this->output->fetch());

        $this->assertCount(3, $rows);
        $this->assertEquals('Remote: echo foo', $rows[0]);
        $this->assertEquals('Remote output from echo foo', $rows[1]);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Remote function need a valid environment. Specify --env parameter.
     *
     * @test
     */
    public function it_should_throw_exception_if_not_connected()
    {
        $this->operations->remote('echo foo');
    }

    /**
     * @test
     */
    public function it_should_allow_to_register_methods()
    {
        $methods = MethodCollection::ofCallables(
            array(
                new CallableMethod('mul', function($x, $y) { return $x * $y;}),
                new CallableMethod('div', function($x, $y) { return $x / $y;}),
            )
        );

        $this->operations->addMethods($methods);

        $this->assertEquals(10, $this->operations->mul(5, 2));
        $this->assertEquals(3, $this->operations->div(9, 3));

    }
}
