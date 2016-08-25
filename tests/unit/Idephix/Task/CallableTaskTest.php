<?php
namespace Idephix\Task;

use Idephix\Context;

class CallableTaskTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_should_build_from_closure()
    {
        $context = $this->prophesize('\Idephix\Context');

        /**
         * A fake task
         * @param Context $context
         * @param $foo
         * @param bool $go
         * @return mixed
         */
        $taskCode = function (Context $context, $foo, $go = false) {
            return $foo;
        };

        $task = CallableTask::buildFromClosure(
            'fooTask',
            $taskCode
        );

        $this->assertEquals('fooTask', $task->name());
        $this->assertEquals('A fake task', $task->description());

        $this->assertCount(3, $task->parameters());
        $this->assertCount(2, $task->userDefinedParameters());

        $closure = $task->code();
        $this->assertEquals('foo', $closure($context->reveal(), 'foo'));
    }
}
