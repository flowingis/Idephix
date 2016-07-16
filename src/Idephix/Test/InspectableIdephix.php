<?php
namespace Idephix\Test;

use Idephix\Config;
use Idephix\Context;
use Idephix\Extension;
use Idephix\IdephixInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InspectableIdephix implements IdephixInterface
{

    private $executedCommands = array();
    private $currentContext;
    private $currentTargetName;
    private $sshClient;
    private $input;
    private $output;

    /**
     * @param Config $config
     * @param OutputInterface $output
     * @param InputInterface $input
     */
    public function __construct($config, OutputInterface $output = null, InputInterface $input = null)
    {
        $this->sshClient = $config->get('sshClient', null);
        $this->input = $input;
        $this->output = $output;
    }

    public function withCurrentTarget(Context $context, $name)
    {
        $idx = clone($this);
        $idx->currentContext = $context;
        $idx->currentTargetName = $name;

        return $idx;
    }

    /**
     * Add a Command to the application.
     * The "--go" parameters should be defined as "$go = false".
     *
     * @param string $name
     * @param callable $code
     * @return \Idephix\IdephixInterface
     */
    public function add($name, $code)
    {
    }

    public function output()
    {
        return $this->output;
    }

    public function input()
    {
        return $this->input;
    }

    public function sshClient()
    {
        return $this->sshClient;
    }

    /**
     * @return null|Context
     */
    public function getCurrentTarget()
    {
        return $this->currentContext;
    }

    public function getCurrentTargetHost()
    {
        // TODO: Implement getCurrentTargetHost() method.
    }

    public function getCurrentTargetName()
    {
        return $this->currentTargetName;
    }

    /**
     * @return null|integer
     */
    public function run()
    {
        // TODO: Implement run() method.
    }

    /**
     * @param Extension $extension
     */
    public function addExtension(Extension $extension)
    {
        // TODO: Implement addExtension() method.
    }

    /**
     * @param $name
     * @return integer 0 success, 1 fail
     */
    public function runTask($name)
    {
        // TODO: Implement runTask() method.
    }

    /**
     * Execute remote command.
     *
     * @param string $cmd command
     * @param boolean $dryRun
     * @return void
     */
    public function remote($cmd, $dryRun = false)
    {
        $this->executedCommands[] = trim(sprintf('Remote: %s %s', $cmd, $dryRun ? 'execute-in-dry-run': ''));
    }

    /**
     * Execute local command.
     *
     * @param string $cmd Command
     * @param boolean $dryRun
     * @param integer $timeout
     *
     * @return string the command output
     */
    public function local($cmd, $dryRun = false, $timeout = 60)
    {
        $this->executedCommands[] = trim(sprintf(
            'Local: %s%susing timeout of: %s',
            $cmd,
            $dryRun ? 'execute-in-dry-run ' : ' ',
            $timeout
        ));
    }

    public function getExecutedCommands()
    {
        return $this->executedCommands;
    }
}
