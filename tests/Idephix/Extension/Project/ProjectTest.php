<?php
namespace Idephix\Extension\Project;

use Idephix\Extension\Project\Project;

class ProjectTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->idx = $this->getMockBuilder('Idephix\Idephix')
                          ->disableOriginalConstructor()
                          ->getMock();

        $this->idx->expects($this->exactly(1))
             ->method('local')
             ->will($this->returnArgument(0));

        $this->idx->expects($this->exactly(1))
             ->method('getCurrentTarget')
             ->will($this->returnValue(array('ssh_params' => array('user' => 'kea'))));

        $this->idx->expects($this->exactly(1))
             ->method('getCurrentTargetHost')
             ->will($this->returnValue('banana.com'));

        $this->project = new Project();
        $this->project->setIdephix($this->idx);

    }

    public function testRsyncProject()
    {
        $result = $this->project->rsyncProject('/a/remote');

        $this->assertEquals("rsync -rlDcz --force --delete --progress  -e 'ssh' ./ kea@banana.com:/a/remote/", $result);
    }

}
