<?php
namespace Idephix\Task;

use Idephix\Task\Parameter\Context;
use Idephix\Task\Parameter\Collection;
use Idephix\Task\Parameter\UserDefined;
use Idephix\Task\Parameter\UserDefinedCollection;

class UserDefinedParameterCollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function it_should_return_only_user_defined_parameters()
    {
        $parameters = Collection::dry();
        $parameters[] = UserDefined::create('foo', 'my foo parameter');
        $parameters[] = Context::create();
        $parameters[] = UserDefined::create('bar', 'my bar paramter');

        $filtered = new UserDefinedCollection($parameters);

        $count = 0;
        foreach ($filtered as $parameter) {
            $this->assertInstanceOf('\Idephix\Task\Parameter\UserDefined', $parameter);
            $count++;
        }

        $this->assertEquals(2, $count);
    }
}
