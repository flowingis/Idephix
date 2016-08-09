<?php
namespace Idephix\Task;

use Idephix\Config;
use Idephix\Idephix;
use Idephix\TaskExecutor;

class CallableTaskTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_should_build_from_closure()
    {
        /**
         * A fake task
         * @param TaskExecutor $idx
         * @param $foo
         * @param bool $go
         * @return mixed
         */
        $task = function (TaskExecutor $idx, $foo, $go = false) {
            return $foo;
        };
        $idx = Idephix::create(TaskCollection::dry(), Config::dry());

        $task = CallableTask::buildFromClosure(
            'fooTask',
            $task
        );

        $this->assertEquals('fooTask', $task->name());
        $this->assertEquals('A fake task', $task->description());

        $this->assertCount(3, $task->parameters());
        $this->assertCount(2, $task->userDefinedParameters());

        $closure = $task->code();
        $this->assertEquals('foo', $closure($idx, 'foo'));
    }
}
