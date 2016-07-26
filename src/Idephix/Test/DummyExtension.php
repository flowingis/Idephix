<?php

namespace Idephix\Test;

use Idephix\Extension;
use Idephix\Extension\IdephixAwareInterface;
use Idephix\IdephixInterface;
use Idephix\Task\Parameter\Collection;
use Idephix\Task\CallableTask;
use Idephix\Task\TaskCollection;

class DummyExtension implements IdephixAwareInterface, Extension
{
    private $tester;
    private $name;

    public function __construct(\PHPUnit_Framework_TestCase $tester, $name)
    {
        $this->tester = $tester;
        $this->name = $name;
    }

    public function setIdephix(IdephixInterface $idx)
    {
        $this->tester->assertTrue(true, 'Set Idephix');
    }

    public function test($return)
    {
        return $this->doTest($return);
    }

    public function update($what)
    {
        return $this->doTest($what);
    }

    /**
     * @param $return
     * @return mixed
     */
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
        return Extension\HelperCollection::ofCallables(
            array(
                new Extension\CallableHelper('test', array($this, 'test'))
            )
        );
    }
}
