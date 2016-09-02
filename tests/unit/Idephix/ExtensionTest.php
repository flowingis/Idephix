<?php
namespace Idephix;

use Idephix\Task\CallableTask;
use Idephix\Test\DummyExtension;
use Idephix\Task\TaskCollection;
use Idephix\Extension\MethodCollection;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Input\StringInput;

class ExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_should_register_methods_from_config()
    {
        $ext = $this->prophesize('\Idephix\Extension\MethodProvider');
        $ext->methods()->willReturn(MethodCollection::dry());

        $conf = array(
            Config::EXTENSIONS => array($ext->reveal())
        );

        $idx = new Idephix(
            Config::fromArray($conf),
            TaskCollection::dry(),
            $output,
            new StringInput('')
        );

        $ext->methods()->shouldHaveBeenCalled();
    }

    /**
     * @test
     */
    public function it_should_register_tasks_from_config()
    {
        $ext = $this->prophesize('\Idephix\Extension\TaskProvider');
        $ext->tasks()->willReturn(TaskCollection::dry());

        $conf = array(
            Config::EXTENSIONS => array($ext->reveal())
        );

        $idx = new Idephix(
            Config::fromArray($conf),
            TaskCollection::dry(),
            $output,
            new StringInput('')
        );

        $ext->tasks()->shouldHaveBeenCalled();
    }

    /**
     * @test
     */
    public function it_should_inject_context()
    {
        $ext = $this->prophesize('\Idephix\Extension\ContextAwareInterface');

        $conf = array(
            Config::EXTENSIONS => array($ext->reveal())
        );

        $idx = new Idephix(
            Config::fromArray($conf),
            TaskCollection::dry(),
            $output,
            new StringInput('')
        );

        $ext->setContext(\Prophecy\Argument::any())->shouldHaveBeenCalled();
    }

    /**
     * @test
     */
    public function it_should_allow_to_override_extension_method()
    {
        $this->fail();

        // $extension = new DummyExtension($this, 'myExtension');

        // $this->idx->addExtension($extension);
        // $this->idx->addTask(CallableTask::buildFromClosure('test', function ($what) { return $what * 2;}));

        // $this->assertEquals(84, $this->idx->test(42));
    }

}
