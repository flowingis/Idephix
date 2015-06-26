<?php
namespace Idephix\File;

use Idephix\IdxSetupCollector;
use Idephix\SSH\SshClient;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StandardIdxFile implements IdxFile
{

    private $file;

    /**
     * @var array
     */
    private $targets;

    /**
     * @var array
     */
    private $libraries;

    /**
     * @var SshClient
     */
    private $sshClient;

    function __construct($file)
    {
        $this->file = $file;

        $idx = $this->collectTaskData($file);

        $this->targets = $idx->getTargets();
        $this->tasks = $idx->getTasks();
        $this->libraries = $idx->getLibraries();
        $this->input = $idx->input();
        $this->output = $idx->output();
        $this->sshClient = $idx->getSshClient();
    }


    public function targets()
    {
        return $this->targets;
    }

    public function sshClient()
    {
        return $this->sshClient;
    }

    public function output()
    {
        return $this->output;
    }

    public function input()
    {
        return $this->input;
    }

    public function tasks()
    {
        return $this->tasks;
    }

    public function libraries()
    {
        return $this->libraries;
    }

    /**
     * @param $file
     * @return IdxSetupCollector
     */
    private function collectTaskData($file)
    {
        $idx = new IdxSetupCollector();
        include $file;

        if (!isset($targets)) {
            $targets = array();
        }

        if (!isset($sshClient)) {
            $sshClient = new SshClient();
        }

        $idx->setTargets($targets);
        $idx->setSshClient($sshClient);

        return $idx;
    }
}