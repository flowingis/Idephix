<?php
namespace Idephix;

use Idephix\Task\CallableTask;
use Idephix\Test\DummyExtension;
use Symfony\Component\Console\Output\StreamOutput;

class ExtensionTest extends \PHPUnit_Framework_TestCase
{
    private $output;

    protected function setUp()
    {
        $this->output = fopen('php://memory', 'r+');
        $output = new StreamOutput($this->output);

        $this->idx = new Idephix(
            Config::fromArray(
                array('envs' => array(), 'ssh_client' => new SSH\SshClient(new Test\SSH\StubProxy()))
            ), $output
        );
    }

    /** @test */
    public function it_should_register_extensions_from_config()
    {
        $extension = new DummyExtension($this, 'deploy');
        $idx = new Idephix(
            Config::fromArray(
                array(
                    Config::EXTENSIONS => array($extension)
                )
            )
        );

        $this->assertEquals(42, $idx->test(42));
    }

    /**
     * @test
     */
    public function it_should_allow_to_use_extension()
    {
        $extension = new DummyExtension($this, 'deploy');
        $this->idx->addExtension($extension);
        $this->assertEquals(42, $this->idx->test(42));
    }

    /**
     * @test
     */
    public function it_should_allow_to_override_extension_method()
    {
        $extension = new DummyExtension($this, 'myExtension');
        $this->idx->addExtension($extension);
        $this->idx->addTask(CallableTask::buildFromClosure('test', function ($what) { return $what * 2;}));
        $this->assertEquals(84, $this->idx->test(42));
    }

    /**
     * @test
     */
    public function it_should_add_task_from_extesions()
    {
        $extension = new DummyExtension($this, 'deploy');
        $this->idx->addExtension($extension);

        $registeredCommands = $this->idx->getApplication()->all();
        $this->assertArrayHasKey('update', $registeredCommands);
        $this->assertInstanceOf('\Idephix\Console\Command', $registeredCommands['update']);
        $this->assertEquals('update', $registeredCommands['update']->getName());
        $this->assertEquals(42, $this->idx->update(42));
    }

    /**
     * @test
     * @expectedException \BadMethodCallException
     */
    public function it_should_throw_exception_calling_unregistered_method()
    {
        $extension = new DummyExtension($this, 'deploy');
        $this->idx->addExtension($extension);

        $this->assertEquals(42, $this->idx->unregisteredMethod(42));
    }
}
