<?php
namespace Idephix;

use Idephix\SSH\SshClient;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LazyIdephix implements IdephixInterface
{

    /**
     * @var array
     */
    private $targets;

    /**
     * @var SshClient
     */
    private $sshClient;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var array
     */
    private $tasks = [];

    /**
     * @var array
     */
    private $libraries = [];

    /**
     * @var IdephixInterface
     */
    private $idx;

    public function __construct(
        array $targets = null,
        SshClient $sshClient = null,
        OutputInterface $output = null,
        InputInterface $input = null
    ) {
        $this->targets = $targets;
        $this->sshClient = $sshClient;
        $this->output = $output;
        $this->input = $input;
    }

    /**
     * @param array $targets
     */
    public function setTargets($targets)
    {
        $this->targets = $targets;
    }

    /**
     * @param SshClient $sshClient
     */
    public function setSshClient(SshClient $sshClient)
    {
        $this->sshClient = $sshClient;
    }

    public function add($name, $code)
    {
        $this->tasks[$name] = $code;
    }

    public function addLibrary($name, $library)
    {
        $this->libraries[$name] = $library;
    }

    public function run()
    {
        $this->idx = new Idephix($this->targets, $this->sshClient, $this->output, $this->input);

        foreach($this->tasks as $name => $task)
        {
            $this->idx->add($name, $task);
        }

        foreach($this->libraries as $name => $library)
        {
            $this->idx->addLibrary($name, $library);
        }

        return $this->idx->run();
    }

    public function getCurrentTarget()
    {
        $this->checkIdxIsRunning();
        return $this->idx->getCurrentTarget();
    }

    public function getCurrentTargetHost()
    {
        $this->checkIdxIsRunning();
        return $this->idx->getCurrentTargetHost();
    }

    public function getCurrentTargetName()
    {
        $this->checkIdxIsRunning();
        return $this->idx->getCurrentTargetName();
    }

    public function runTask($name)
    {
        $this->checkIdxIsRunning();
        return $this->idx->runTask($name);
    }

    public function remote($cmd, $dryRun = false)
    {
        $this->checkIdxIsRunning();
        return $this->idx->remote($cmd, $dryRun);
    }

    public function local($cmd, $dryRun = false, $timeout = 60)
    {
        $this->checkIdxIsRunning();
        return $this->idx->local($cmd, $dryRun, $timeout);

    }

    private function checkIdxIsRunning()
    {
        if(is_numeric($this->idx)){
            throw new \RuntimeException('You must call the run method before performing any action');
        }
    }
}