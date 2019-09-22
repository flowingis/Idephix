<?php
namespace Idephix\Task;

use Idephix\Task\Parameter\Collection;
use Idephix\Task\Parameter\UserDefined;

class ParameterCollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     * @expectedException \DomainException
     */
    public function it_should_only_accept_parameter_definition()
    {
        $collection = Collection::dry();
        $collection[] = new \stdClass();
    }

    /** @test */
    public function it_should_create_from_array()
    {
        $collection = Collection::createFromArray(
            array(
                'foo' => array('description' => 'my foo param'),
                'bar' => array('description' => 'my bar param', 'defaultValue' => 'foobar'),
            )
        );

        $this->assertCount(2, $collection);
        $this->assertEquals(UserDefined::create('foo', 'my foo param'), $collection[0]);
        $this->assertEquals(UserDefined::create('bar', 'my bar param', 'foobar'), $collection[1]);
    }
}
