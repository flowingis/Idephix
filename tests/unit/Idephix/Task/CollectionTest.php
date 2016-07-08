<?php
namespace Idephix\Task;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_should_allow_array_access()
    {
        $collection = TestCollection::ofArray(array(new \stdClass()));
        $this->assertEquals(new \stdClass(), $collection[0]);
    }
}

class TestCollection extends Collection
{
    public static function ofArray($array)
    {
        return new static(new \ArrayIterator($array));
    }
    
    public function offsetSet($offset, $value)
    {
        $this->getInnerIterator()->offsetSet($offset, $value);
    }
}