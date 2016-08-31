<?php

namespace Idephix\Test;

use Idephix\Extension\MethodProvider;
use Idephix\Extension\TaskProvider;
use Idephix\Extension\ContextAwareInterface;
use Idephix\Context;
use Idephix\Task\Parameter\Collection;
use Idephix\Task\CallableTask;
use Idephix\Task\TaskCollection;

class DummyExtension implements ContextAwareInterface, MethodProvider, TaskProvider
{
    private $tester;
    private $name;

    public function __construct(\PHPUnit_Framework_TestCase $tester, $name)
    {
        $this->tester = $tester;
        $this->name = $name;
    }

    public function setContext(Context $idx)
    {
        $this->tester->assertTrue(true, 'Set Context');
    }

    public function test($return)
    {
        return $this->doTest($return);
    }

    public function update($what)
    {
        return $this->doTest($what);
    }

    private function doTest($return)
    {
        return $return;
    }

    public function name()
    {
        return $this->name;
    }

    public function unregisteredMethod($return)
    {
        return $return;
    }

    /** @return TaskCollection */
    public function tasks()
    {
        $collection = TaskCollection::dry();
        $collection[] = new CallableTask(
            'update', 'An exposed task by DummyExtension',
            array($this, 'update'),
            Collection::createFromArray(array('return' => array('description' => 'what do you want back')))
        );

        return $collection;
    }

    /** @return array of callable */
    public function methods()
    {
        return Extension\MethodCollection::ofCallables(
            array(
                new Extension\CallableMethod('test', array($this, 'test'))
            )
        );
    }
}
