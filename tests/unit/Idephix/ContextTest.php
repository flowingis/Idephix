<?php
namespace Idephix;

class ContextTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_should_not_have_a_defualt_target_name()
    {
        $context = $this->buildContext();

        $this->assertNull($context['target.name']);
        $this->assertNull($context['target.host']);
        $this->assertNull($context->targetName());
        $this->assertNull($context->targetHost());
    }

    /** @test */
    public function it_should_allow_to_define_a_target_name()
    {
        $idx = $this->prophesize('\Idephix\Context')->reveal();
        $context = Context::dry($idx)
            ->target(
                'prod',
                Dictionary::fromArray(
                    array(
                        'hosts' => array('127.0.0.1', 'localhost', '10.10.10.10')
                    )
                )
            );

        $this->assertEquals('prod', $context['target.name']);
        $this->assertNull($context['target.host']);
        $this->assertEquals('prod', $context->targetName());
        $this->assertNull($context->targetHost());
    }

    /** @test */
    public function it_should_allow_to_iterate_over_hosts()
    {
        $idx = $this->prophesize('\Idephix\Context')->reveal();
        $targetData = array(
            'hosts' => array('127.0.0.1', 'localhost', '10.10.10.10')
        );

        $context = Context::dry($idx)
            ->target(
                'prod',
                Dictionary::fromArray(
                    $targetData
                )
            );

        foreach ($context as $hostCount => $hostRelatedContext) {
            $this->assertInstanceOf('\Idephix\Context', $hostRelatedContext);

            $this->assertEquals('prod', $context['target.name']);
            $this->assertEquals('prod', $context->targetName());

            $this->assertEquals($targetData['hosts'][$hostCount], $hostRelatedContext['target.host']);
            $this->assertEquals($targetData['hosts'][$hostCount], $hostRelatedContext->targetHost());
        }

        $this->assertEquals(2, $hostCount);
    }

    /** @test */
    public function it_should_allow_to_iterate_over_missing_hosts()
    {
        $idx = $this->prophesize('\Idephix\Context')->reveal();
        $targetData = array('foo' => 'bar');

        $context = Context::dry($idx)
            ->target(
                'prod',
                Dictionary::fromArray(
                    $targetData
                )
            );

        foreach ($context as $hostCount => $hostRelatedContext) {
            $this->assertInstanceOf('\Idephix\Context', $hostRelatedContext);

            $this->assertEquals('prod', $context['target.name']);
            $this->assertEquals('prod', $context->targetName());

            $this->assertNull($hostRelatedContext['target.host']);
            $this->assertNull($hostRelatedContext->targetHost());
        }

        $this->assertEquals(0, $hostCount);
    }

    /**
     * @test
     */
    public function it_should_allow_to_retrieve_value_as_path()
    {
        $idx = $this->prophesize('\Idephix\Context')->reveal();

        $context = Context::dry($idx)
            ->target('prod', Dictionary::fromArray(array('foo' => '/var/www', 'bar' => '/var/www/')));

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

    /** @test */
    public function it_should_return_local_output()
    {
        $idx = $this->prophesize('\Idephix\Context');
        $idx->local('foo', false, 60)->willReturn('bar');

        $context = Context::dry($idx->reveal());

        $this->assertEquals('bar', $context->local('foo'));
    }

    /**
     * @return Context
     */
    private function buildContext()
    {
        $idx = $this->prophesize('\Idephix\Context')->reveal();
        $contextData = array(
            'hosts' => array('127.0.0.1', 'localhost', '10.10.10.10')
        );
        $context = new Context(Dictionary::fromArray($contextData), $idx);

        return $context;
    }
}
