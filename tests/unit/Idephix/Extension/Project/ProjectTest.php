<?php

namespace Idephix\Extension\Project;

use Idephix\Context;

class ProjectTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->idx = $this->getMock('\Idephix\IdephixInterface');

        $this->idx->expects($this->exactly(1))
             ->method('local')
             ->will($this->returnArgument(0));

        $this->idx->expects($this->exactly(1))
             ->method('getCurrentTargetHost')
             ->will($this->returnValue('banana.com'));

        $this->project = new Rsync();
        $this->project->setIdephix($this->idx);
    }

    public function testRsyncProject()
    {
        $this->idx->expects($this->exactly(1))
          ->method('getCurrentTarget')
          ->will($this->returnValue(Context::fromArray(array('ssh_params' => array('user' => 'kea')))));

        $result = $this->project->rsyncProject('/a/remote', './from');

        $this->assertEquals("rsync -rlDcz --force --delete --progress  -e 'ssh' ./from kea@banana.com:/a/remote/", $result);
    }

    public function testRsyncProjectWithCustomPort()
    {
        $this->idx->expects($this->exactly(1))
          ->method('getCurrentTarget')
          ->will($this->returnValue(Context::fromArray(array('ssh_params' => array('user' => 'kea', 'port' => 20817)))));

        $result = $this->project->rsyncProject('/a/remote', './from');

        $this->assertEquals("rsync -rlDcz --force --delete --progress  -e 'ssh -p 20817' ./from kea@banana.com:/a/remote/", $result);
    }
}
