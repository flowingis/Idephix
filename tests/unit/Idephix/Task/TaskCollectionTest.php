<?php
namespace Idephix\Task;

class TaskCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException \DomainException
     */
    public function it_should_only_accept_task_definition()
    {
        $collection = TaskCollection::ofArray(array(new \stdClass()));
        foreach ($collection as $task) {
        }
    }

    /** @test */
    public function it_should_accept_task_definition()
    {
        $collection = TaskCollection::ofArray(array(new Task('foo', 'foo param')));
        foreach ($collection as $task) {
        }
    }
}
