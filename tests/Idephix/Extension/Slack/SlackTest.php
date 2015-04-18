<?php

namespace Idephix\Extension\Slack;

use Idephix\Tests\Test\IdephixTestCase;

//mock curl_exec
function curl_exec($ch, $error = false)
{
    if ($error) {
        return false;
    }

    return 'ok';
}

class SlackTest extends IdephixTestCase
{
    public function setUp()
    {
        $this->idx = $this->getMockBuilder('Idephix\Idephix')
            ->disableOriginalConstructor()
            ->getMock();

        $this->idx->expects($this->exactly(1))
            ->method('local')
            ->will($this->returnArgument(0));

        $this->slack = new Slack();
        $this->slack->setIdephix($this->idx);
    }

    public function testSendMessage()
    {
        $response = $this->slack->sendToSlack('ciao');
        $this->assertEquals($response, 'ok');
    }
}