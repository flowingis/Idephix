<?php
namespace Idephix\Task;

class UserDefinedParameterCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_should_return_only_user_defined_parameters()
    {
        $parameters = ParameterCollection::dry();
        $parameters[] = UserDefinedParameter::create('foo', 'my foo parameter');
        $parameters[] = IdephixParameter::create();
        $parameters[] = UserDefinedParameter::create('bar', 'my bar paramter');

        $filtered = new UserDefinedParameterCollection($parameters);

        $count = 0;
        foreach ($filtered as $parameter) {
            $this->assertInstanceOf('\Idephix\Task\UserDefinedParameter', $parameter);
            $count++;
        }

        $this->assertEquals(2, $count);
    }
}
