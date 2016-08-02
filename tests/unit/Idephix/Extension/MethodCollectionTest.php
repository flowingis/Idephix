<?php
namespace Idephix\Extension;

class MethodCollectionTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_should_be_constructed_from_methods()
    {
        $method = $this->prophesize('\Idephix\Extension\Method')->reveal();
        $collection = MethodCollection::ofCallables(array($method));

        $this->assertEquals(1, $collection->count());
    }

    /** @test */
    public function it_should_be_merged()
    {
        $fooMethod = $this->prophesize('\Idephix\Extension\Method');
        $fooMethod->name()->willReturn('foo');

        $barMethod = $this->prophesize('\Idephix\Extension\Method');
        $barMethod->name()->willReturn('bar');

        $collection = MethodCollection::ofCallables(array($fooMethod->reveal()));
        $otherCollection = MethodCollection::ofCallables(array($barMethod->reveal()));

        $this->assertEquals(1, $collection->count());
        $this->assertEquals(1, $otherCollection->count());
        $this->assertEquals(2, $otherCollection->merge($collection)->count());
    }

    /**
     * @test
     */
    public function it_should_execute_by_name()
    {
        $collection = MethodCollection::ofCallables(array(
            $foo = new StubMethod('foo'),
            $bar = new StubMethod('bar'),
        ));

        $collection->execute('foo', array('arg1', 'arg2'));

        $this->assertFalse($bar->invoked);
        $this->assertTrue($foo->invoked);
        $this->assertEquals(array('arg1', 'arg2'), $foo->args);
    }

    /**
     * @test
     * @expectedException \Idephix\Exception\MissingMethodException
     */
    public function it_should_throw_exception_for_method_not_found()
    {
        MethodCollection::dry()->execute('foo');
    }
}

class StubMethod implements Method
{
    public $invoked = false;
    public $args;
    private $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function name()
    {
        return $this->name;
    }

    public function __invoke()
    {
        $this->invoked = true;
        $this->args = func_get_args();
    }
}
