<?php

namespace Idephix;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Idephix\SSH\SshClient;

interface IdephixInterface
{
    /**
     * @param array $targets
     * @param SshClient $sshClient
     * @param OutputInterface $output
     * @param InputInterface $input
     * @return \Idephix\IdephixInterface
     */
    public function __construct(array $targets = null, SshClient $sshClient = null, OutputInterface $output = null, InputInterface $input = null);

    /**
     * Add a Command to the application.
     * The "--go" parameters should be defined as "$go = false".
     *
     * @param string $name
     * @param callable $code
     * @return \Idephix\IdephixInterface
     */
    public function add($name, $code);

    public function output();

    public function input();

    /**
     * @return null|\Idephix\Config\Targets\Targets
     */
    public function getCurrentTarget();

    public function getCurrentTargetHost();

    public function getCurrentTargetName();

    /**
     * @return null|integer
     */
    public function run();

    /**
     * @param string $name
     * @param object $library
     * @return void
     */
    public function addLibrary($name, $library);

    /**
     * @param $name
     * @return integer 0 success, 1 fail
     */
    public function runTask($name);

    /**
     * Execute remote command.
     *
     * @param string  $cmd    command
     * @param boolean $dryRun
     * @return void
     */
    public function remote($cmd, $dryRun = false);

    /**
     * Execute local command.
     *
     * @param string $cmd Command
     * @param boolean $dryRun
     * @param integer $timeout
     *
     * @return string the command output
     */
    public function local($cmd, $dryRun = false, $timeout = 60);
}
