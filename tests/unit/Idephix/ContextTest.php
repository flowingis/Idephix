<?php
namespace Idephix;

class ContextTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_should_not_have_a_default_target_name()
    {
        $context = $this->buildContext();

        $this->assertNull($context['env.name']);
        $this->assertNull($context['env.host']);
        $this->assertNull($context->currentEnvName());
        $this->assertNull($context->currentHost());
    }

    /** @test */
    public function it_should_allow_to_define_a_target_name()
    {
        $idx = $this->prophesize('\Idephix\Context')->reveal();
        $context = Context::dry($idx)
            ->env(
                'prod',
                Dictionary::fromArray(
                    array(
                        'hosts' => array('127.0.0.1', 'localhost', '10.10.10.10')
                    )
                )
            );

        $this->assertEquals('prod', $context['env.name']);
        $this->assertNull($context['env.host']);
        $this->assertEquals('prod', $context->currentEnvName());
        $this->assertNull($context->currentHost());
    }

    /** @test */
    public function it_should_allow_to_iterate_over_hosts()
    {
        $idx = $this->prophesize('\Idephix\Context')->reveal();
        $targetData = array(
            'hosts' => array('127.0.0.1', 'localhost', '10.10.10.10')
        );

        $context = Context::dry($idx)
            ->env(
                'prod',
                Dictionary::fromArray(
                    $targetData
                )
            );

        foreach ($context as $hostCount => $hostRelatedContext) {
            $this->assertInstanceOf('\Idephix\Context', $hostRelatedContext);

            $this->assertEquals('prod', $context['env.name']);
            $this->assertEquals('prod', $context->currentEnvName());

            $this->assertEquals($targetData['hosts'][$hostCount], $hostRelatedContext['env.host']);
            $this->assertEquals($targetData['hosts'][$hostCount], $hostRelatedContext->currentHost());
        }

        $this->assertEquals(2, $hostCount);
    }

    /** @test */
    public function it_should_allow_to_iterate_over_missing_hosts()
    {
        $idx = $this->prophesize('\Idephix\Context')->reveal();
        $targetData = array('foo' => 'bar');

        $context = Context::dry($idx)
            ->env(
                'prod',
                Dictionary::fromArray(
                    $targetData
                )
            );

        foreach ($context as $hostCount => $hostRelatedContext) {
            $this->assertInstanceOf('\Idephix\Context', $hostRelatedContext);

            $this->assertEquals('prod', $context['env.name']);
            $this->assertEquals('prod', $context->currentEnvName());

            $this->assertNull($hostRelatedContext['env.host']);
            $this->assertNull($hostRelatedContext->currentHost());
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
            ->env('prod', Dictionary::fromArray(array('foo' => '/var/www', 'bar' => '/var/www/')));

        $this->assertEquals('/var/www/', $context->getAsPath('foo'));
        $this->assertEquals('/var/www/', $context->getAsPath('bar'));
    }

    /** @test */
    public function it_should_run_task_sending_multiple_arguments()
    {
        $idx = $this->prophesize('\Idephix\Context');
        $idx->execute('mycommand', 'foo', 'bar')->shouldBeCalled();

        $context = Context::dry($idx->reveal());
        $context->execute('mycommand', 'foo', 'bar');
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
