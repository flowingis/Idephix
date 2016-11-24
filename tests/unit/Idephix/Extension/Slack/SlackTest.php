<?php

namespace Idephix\Extension\Slack;

use Idephix\Context;

//mock curl_exec
function curl_exec($ch, $error = false)
{
    if ($error) {
        return false;
    }

    return 'ok';
}

class SlackTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->context = $this->prophesize('\Idephix\Context');

        $this->slack = new Slack();
        $this->slack->setContext($this->context->reveal());
    }

    public function testSendMessage()
    {
        $this->context
             ->local("echo 'Message sent to slack channel'")
             ->willReturn(0);

        $response = $this->slack->sendToSlack('ciao');

        $this->assertEquals($response, 'ok');
    }
}
