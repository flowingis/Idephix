<?php
namespace Idephix\Task;

class ParameterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_should_be_definable_as_flag()
    {
        $parameter = UserDefinedParameter::create('foo', 'my foo param', false);
        $this->assertTrue($parameter->isFlagOption());
    }

    /** @test */
    public function it_should_be_definable_as_optional()
    {
        $parameter = UserDefinedParameter::create('foo', 'my foo param', 'bar');
        $this->assertTrue($parameter->isOptional());
    }

    /** @test */
    public function it_should_not_be_nullable_by_default()
    {
        $parameter = UserDefinedParameter::create('foo', 'my foo param');
        $this->assertFalse($parameter->isOptional());
    }
}
