<?php

namespace Idephix\Extension\Project;

use Idephix\Context;

class ProjectTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->context = $this->prophesize('\Idephix\Context');

        $this->project = new Rsync();
        $this->project->setContext($this->context->reveal());
    }

    public function testRsyncProject()
    {
        $expected = "rsync -rlDcz --force --delete --progress  -e 'ssh -p 22' ./from kea@banana.com:/a/remote/";

        $this->context->local($expected)->shouldBeCalled();
        $this->context->getSshParams()->willReturn(array('user' => 'kea'));
        $this->context->getHosts()->willReturn(array('mela.com', 'banana.com'));
        $this->context->getCurrentHost()->willReturn('banana.com');

        $result = $this->project->rsyncProject('/a/remote', './from');
    }

    public function testRysncWithoutUser()
    {
        $expected = "rsync -rlDcz --force --delete --progress  -e 'ssh -p 22' ./from banana.com:/a/remote/";

        $this->context->local($expected)->shouldBeCalled();
        $this->context->getSshParams()->willReturn(array());
        $this->context->getHosts()->willReturn(array('mela.com', 'banana.com'));
        $this->context->getCurrentHost()->willReturn('banana.com');

        $result = $this->project->rsyncProject('/a/remote', './from');
    }

    public function testRsyncProjectWithCustomPort()
    {
        $expected = "rsync -rlDcz --force --delete --progress  -e 'ssh -p 20817' ./from kea@banana.com:/a/remote/";

        $this->context->local($expected)->shouldBeCalled();
        $this->context->getSshParams()->willReturn(array('user' => 'kea', 'port' => 20817));
        $this->context->getHosts()->willReturn(array('mela.com', 'banana.com'));
        $this->context->getCurrentHost()->willReturn('banana.com');

        $result = $this->project->rsyncProject('/a/remote', './from');
    }
}
