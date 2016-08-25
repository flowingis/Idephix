<?php
namespace Idephix;

class ContextTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->executor = $this->prophesize('\Idephix\TaskExecutor');
        $this->operations = $this->prophesize('\Idephix\Operations');
        $this->config = $this->prophesize('\Idephix\Config');

        $this->context = new Context(
            $this->executor->reveal(),
            $this->operations->reveal(),
            $this->config->reveal()
        );
    }

    /** @test */
    public function it_should_not_have_a_default_target_name()
    {
        $this->assertNull($this->context->currentEnvName());
        $this->assertNull($this->context->currentHost());
    }

    /** @test */
    public function it_should_allow_to_define_a_target_name()
    {
        $this->context->setEnv('prod');

        $this->assertEquals('prod', $this->context->getEnv());
        $this->assertNull($this->context->getHosts());
    }

    /** @test */
    public function it_should_allow_to_iterate_over_hosts()
    {
        $this->config
             ->get('envs.prod.hosts')
             ->willReturn(array('127.0.0.1', 'localhost', '10.10.10.10'));

        $this->context
             ->setEnv('prod');

        $this->assertCount(3, $this->context->getHosts());
    }

    /** @test */
    public function it_should_allow_to_iterate_over_missing_hosts()
    {
        $this->config
             ->get('envs.prod.hosts')
             ->shouldBeCalled();

        $this->context
             ->setEnv('prod');

        $this->assertEquals(0, $this->context->getHosts());
    }

    /** @test */
    public function it_should_run_task_sending_multiple_arguments()
    {
        $this->context->mycommand('foo', 'bar');

        $this->executor
             ->runTask('mycommand', array('foo', 'bar'))
             ->shouldHaveBeenCalled();
    }

    /** @test */
    public function it_should_return_local_output()
    {
        $this->operations
             ->local('foo', false, 60)
             ->willReturn('bar');

        $this->assertEquals('bar', $this->context->local('foo'));
    }

    /**
     * @test
     * @expectedException RunTimeException
     */
    public function it_should_throw_exception()
    {
        $this->context->getHosts();
    }

    /** @test */
    public function it_should_return_current_host()
    {
        $conf = array(
            'envs' => array(
                'prod' => array(
                    'hosts' => array('1', '2', '3')
                )
            )
        );

        $config = Config::fromArray($conf);
        $executor = $this->prophesize('\Idephix\TaskExecutor');
        $operations = $this->prophesize('\Idephix\Operations');

        $context = new Context(
            $executor->reveal(),
            $operations->reveal(),
            $config
        );

        $context->setEnv('prod');
        $hosts = $context->getHosts();

        $this->assertEquals('1', $context->getCurrentHost());
        $hosts->next();

        $this->assertEquals('2', $context->getCurrentHost());
        $hosts->next();

        $this->assertEquals('3', $context->getCurrentHost());
        $hosts->next();

        $this->assertNull($context->getCurrentHost());
    }
}
