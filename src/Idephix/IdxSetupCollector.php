<?php
namespace Idephix;

use Idephix\SSH\SshClient;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class IdxSetupCollector implements IdephixInterface
{

    /**
     * @var array
     */
    private $targets = array();

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
    private $tasks = array();

    /**
     * @var array
     */
    private $libraries = array();

    public function __construct(
        array $targets = array(),
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

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
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
        $this->disableRun();
    }

    public function getCurrentTarget()
    {
        $this->disableRun();
    }

    public function getCurrentTargetHost()
    {
        $this->disableRun();
    }

    public function getCurrentTargetName()
    {
        $this->disableRun();
    }

    public function runTask($name)
    {
        $this->disableRun();
    }

    public function remote($cmd, $dryRun = false)
    {
        $this->disableRun();
    }

    public function local($cmd, $dryRun = false, $timeout = 60)
    {
        $this->disableRun();
    }

    /**
     * @return array
     */
    public function getTargets()
    {
        return $this->targets;
    }

    /**
     * @return SshClient
     */
    public function getSshClient()
    {
        return $this->sshClient;
    }

    /**
     * @return array
     */
    public function getTasks()
    {
        return $this->tasks;
    }

    /**
     * @return array
     */
    public function getLibraries()
    {
        return $this->libraries;
    }

    private function disableRun()
    {
        throw new \RuntimeException(
            'This instance of IdephixInterface is not runnable, you should use it only for collecting setup information'
        );
    }

    public function output()
    {
        return $this->output;
    }

    public function input()
    {
        return $this->input;
    }
}
