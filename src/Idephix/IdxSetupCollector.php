<?php
namespace Idephix;

use Idephix\SSH\SshClient;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class IdxSetupCollector implements IdephixInterface
{
    /**
     * @var Config
     */
    private $executionContext;

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
        Config $env,
        OutputInterface $output = null,
        InputInterface $input = null
    ) {
        $this->executionContext = $env;
        $this->output = $output;
        $this->input = $input;
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

    public function getExecutionContext()
    {
        return $this->executionContext;
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
