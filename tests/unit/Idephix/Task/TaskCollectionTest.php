<?php
namespace Idephix\Task;

use Idephix\Task\Parameter\Collection;

class TaskCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException \DomainException
     */
    public function it_should_only_accept_task_definition()
    {
        $collection = TaskCollection::dry();
        $collection[] = new \stdClass();
    }

    /** @test */
    public function it_should_create_task_from_functions()
    {
        $idxFileContent = <<<'EOD'
<?php
/**
 * This is foo description
 *
 */
function foo($foo, $bar){ echo $bar; }

EOD;

        $collection = TaskCollection::ofFunctions($idxFileContent);
        $this->assertCount(1, $collection);
        $this->assertInstanceOf('\Idephix\Task\CallableTask', $collection[0]);
    }

    /**
     * @test
     */
    public function it_should_remove_underscore_from_task_name()
    {
        $idxFileContent =<<<'EOD'
<?php

function _echo_($bar){ echo $bar; }

EOD;

        $collection = TaskCollection::ofFunctions($idxFileContent);
        $this->assertCount(1, $collection);

        $task = $collection[0];
        $this->assertInstanceOf('\Idephix\Task\CallableTask', $task);
        $this->assertEquals('echo', $task->name());
    }

    /** @test */
    public function it_should_know_if_has_a_task()
    {
        $collection = TaskCollection::ofTasks(array(new DummyTask()));

        $this->assertFalse($collection->has('missingCommand'));
        $this->assertTrue($collection->has('dummy'));
    }
}


class DummyTask implements Task
{

    public function name()
    {
        return 'dummy';
    }

    public function description()
    {
        // TODO: Implement description() method.
    }

    /**
     * @return Collection
     */
    public function parameters()
    {
        // TODO: Implement parameters() method.
    }

    public function userDefinedParameters()
    {
        // TODO: Implement userDefinedParameters() method.
    }

    /**
     * @return callable
     */
    public function code()
    {
        // TODO: Implement code() method.
    }
}
