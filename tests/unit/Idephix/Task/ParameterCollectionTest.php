<?php
namespace Idephix\Task;

class ParameterCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException \DomainException
     */
    public function it_should_only_accept_parameter_definition()
    {
        $collection = ParameterCollection::dry();
        $collection[] = new \stdClass();
    }

    /** @test */
    public function it_should_create_from_array()
    {
        $collection = ParameterCollection::createFromArray(
            array(
                'foo' => array('description' => 'my foo param'),
                'bar' => array('description' => 'my bar param', 'defaultValue' => 'foobar'),
            )
        );

        $this->assertCount(2, $collection);
        $this->assertEquals(Parameter::create('foo', 'my foo param'), $collection[0]);
        $this->assertEquals(Parameter::create('bar', 'my bar param', 'foobar'), $collection[1]);
    }
}
