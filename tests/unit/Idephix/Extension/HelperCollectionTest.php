<?php
namespace Idephix\Extension;

class HelperCollectionTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_should_be_constructed_from_methods()
    {
        $method = $this->prophesize('\Idephix\Extension\Helper')->reveal();
        $collection = HelperCollection::ofCallables(array($method));

        $this->assertEquals(1, $collection->count());
    }

    /** @test */
    public function it_should_be_merged()
    {
        $fooMethod = $this->prophesize('\Idephix\Extension\Helper');
        $fooMethod->name()->willReturn('foo');

        $barMethod = $this->prophesize('\Idephix\Extension\Helper');
        $barMethod->name()->willReturn('bar');

        $collection = HelperCollection::ofCallables(array($fooMethod->reveal()));
        $otherCollection = HelperCollection::ofCallables(array($barMethod->reveal()));

        $this->assertEquals(1, $collection->count());
        $this->assertEquals(1, $otherCollection->count());
        $this->assertEquals(2, $otherCollection->merge($collection)->count());
    }

    /**
     * @test
     */
    public function it_should_execute_by_name()
    {
        $collection = HelperCollection::ofCallables(array(
            $foo = new StubHelper('foo'),
            $bar = new StubHelper('bar'),
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
        HelperCollection::dry()->execute('foo');
    }
}

class StubHelper implements Helper
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
