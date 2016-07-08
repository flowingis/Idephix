<?php
namespace Idephix\Task;

class ParameterCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_should_only_accept_parameter_definition()
    {
        $collection = ParameterCollection::ofArray(array(new \stdClass()));
        $this->assertCount(0, $collection);

        try {
            $collection = ParameterCollection::dry();
            $collection[] = new \stdClass();

            $this->fail('Should accept only Task object');
        } catch (\DomainException $e) {
            $this->assertInstanceOf('\DomainException', $e);
        }
    }

    /** @test */
    public function it_should_create_from_array()
    {
        $collection = ParameterCollection::create(
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