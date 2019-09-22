<?php

namespace Idephix\Extension\Project;

use Idephix\Context;
use Idephix\Dictionary;

class ProjectTest extends \PHPUnit\Framework\TestCase
{
    public function setUp()
    {
        $this->idx = $this->getMockBuilder('\Idephix\Idephix')
            ->disableOriginalConstructor()
            ->getMock();

        $this->idx->expects($this->exactly(1))
             ->method('local')
             ->will($this->returnArgument(0));

        $this->project = new Rsync();
        $this->project->setIdephix($this->idx);
    }

    public function testRsyncProject()
    {
        $this->idx->expects($this->exactly(1))
            ->method('getContext')
            ->will(
                $this->returnValue(
                    new Context(
                        Dictionary::fromArray(
                            array(
                                'env' => array('name' => 'prod', 'host' => 'banana.com'),
                                'hosts' => array('mela.com', 'banana.com'),
                                'ssh_params' => array('user' => 'kea')
                            )
                        ),
                        $this->idx
                    )
                )
            );

        $result = $this->project->rsyncProject('/a/remote', './from');

        $this->assertEquals("rsync -rlDcz --force --delete --progress  -e 'ssh' ./from kea@banana.com:/a/remote/", $result);
    }

    public function testRysncWithoutUser()
    {
        $this->idx->expects($this->exactly(1))
            ->method('getContext')
            ->will(
                $this->returnValue(
                    new Context(
                        Dictionary::fromArray(
                            array(
                                'env' => array('name' => 'prod', 'host' => 'banana.com'),
                                'hosts' => array('mela.com', 'banana.com'),
                                'ssh_params' => array()
                            )
                        ),
                        $this->idx
                    )
                )
            );

        $result = $this->project->rsyncProject('/a/remote', './from');

        $this->assertEquals("rsync -rlDcz --force --delete --progress  -e 'ssh' ./from banana.com:/a/remote/", $result);
    }

    public function testRsyncProjectWithCustomPort()
    {
        $this->idx->expects($this->exactly(1))
            ->method('getContext')
            ->will(
                $this->returnValue(
                    new Context(
                        Dictionary::fromArray(
                            array(
                                'env' => array('name' => 'prod', 'host' => 'banana.com'),
                                'hosts' => array('mela.com', 'banana.com'),
                                'ssh_params' => array('user' => 'kea', 'port' => 20817)
                            )
                        ),
                        $this->idx
                    )
                )
            );

        $result = $this->project->rsyncProject('/a/remote', './from');

        $this->assertEquals("rsync -rlDcz --force --delete --progress  -e 'ssh -p 20817' ./from kea@banana.com:/a/remote/", $result);
    }
}
