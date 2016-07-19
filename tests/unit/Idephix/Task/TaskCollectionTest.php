<?php
namespace Idephix\Task;

use Idephix\Task\Parameter\Idephix;
use Idephix\Task\Parameter\Collection;
use Idephix\Task\Parameter\UserDefined;

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

        $task = $collection[0];

        $this->assertTaskEqual(
            new CallableTask(
                'foo',
                'This is foo description',
                function ($bar) {
                    echo $bar;
                },
                Collection::createFromArray(
                    array('foo' => array('description' => ''), 'bar' => array('description' => ''))
                )
            ),
            $task
        );
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

        $this->assertTaskEqual(
            new CallableTask(
                'echo',
                '',
                function ($bar) {
                    echo $bar;
                },
                Collection::createFromArray(array('bar' => array('description' => '')))
            ),
            $task
        );
    }

    /** @test */
    public function it_should_recognize_idx_param()
    {
        $idxFileContent =<<<'EOD'
<?php

use Idephix\Idephix as Idx;

function foo(Idx $idx, $bar){echo 'bar';};

EOD;

        $collection = TaskCollection::ofFunctions($idxFileContent);
        $this->assertCount(1, $collection);

        $task = $collection[0];

        $expectedParams = Collection::dry();
        $expectedParams[] = Idephix::create();
        $expectedParams[] = UserDefined::create('bar', '');

        $this->assertTaskEqual(
            new CallableTask(
                'foo',
                '',
                function ($bar) {
                    echo $bar;
                },
                $expectedParams
            ),
            $task
        );
    }

    /**
     * @param $actual
     */
    private function assertTaskEqual(Task $expected, Task $actual)
    {
        $this->assertEquals(
            $expected,
            $actual
        );

        $this->assertEquals(iterator_to_array($expected->parameters()), iterator_to_array($actual->parameters()));
    }
}
