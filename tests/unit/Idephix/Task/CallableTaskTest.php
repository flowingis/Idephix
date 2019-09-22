<?php
namespace Idephix\Task;

use Idephix\Config;
use Idephix\Idephix;
use Idephix\Context;

class CallableTaskTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function it_should_build_from_closure()
    {
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

        $context = Context::dry(Idephix::create(TaskCollection::dry(), Config::dry()));

        $task = CallableTask::buildFromClosure(
            'fooTask',
            $taskCode
        );

        $this->assertEquals('fooTask', $task->name());
        $this->assertEquals('A fake task', $task->description());

        $this->assertCount(3, $task->parameters());
        $this->assertCount(2, $task->userDefinedParameters());

        $closure = $task->code();
        $this->assertEquals('foo', $closure($context, 'foo'));
    }
}
