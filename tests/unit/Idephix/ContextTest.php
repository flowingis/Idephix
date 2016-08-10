<?php
namespace Idephix;

class ContextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_should_allow_to_retrieve_value_as_path()
    {
        $idx = $this->prophesize('\Idephix\Context')->reveal();

        $context = Context::fromArray(array('foo' => '/var/www', 'bar' => '/var/www/'), $idx);

        $this->assertEquals('/var/www/', $context->getAsPath('foo'));
        $this->assertEquals('/var/www/', $context->getAsPath('bar'));
    }

    /** @test */
    public function it_should_run_task_sending_multiple_arguments()
    {
        $idx = $this->prophesize('\Idephix\Context');
        $idx->runTask('mycommand', 'foo', 'bar')->shouldBeCalled();

        $context = Context::dry($idx->reveal());
        $context->runTask('mycommand', 'foo', 'bar');
    }
}
