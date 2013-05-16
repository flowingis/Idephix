<?php

namespace Idephix;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Idephix\SSH\SshClient;

interface IdephixInterface
{
    public function __construct(array $targets = null, SshClient $sshClient = null, OutputInterface $output = null, InputInterface $input = null);

    /**
     * Add a Command to the application.
     * The "--go" parameters should be defined as "$go = false".
     *
     * @param string  $name
     * @param Closure $code
     *
     * @return Idephix
     */
    public function add($name, \Closure $code);

    public function getCurrentTarget();

    public function getCurrentTargetHost();

    public function getCurrentTargetName();

    public function run();

    public function addLibrary($name, $library);

    public function runTask($name);

    /**
     * Execute remote command.
     *
     * @param string  $cmd command
     * @param boolean $dryRun
     */
    public function remote($cmd, $dryRun = false);

    /**
     * Execute local command.
     *
     * @param string $cmd Command
     *
     * @return string the command output
     */
    public function local($cmd, $dryRun = false);
}
