<?php

namespace Idephix;

interface IdephixInterface
{
    /**
     * Add a Command to the application.
     * The "--go" parameters should be defined as "$go = false".
     *
     * @param string $name
     * @param \Closure $code
     * @return IdephixInterface
     */
    public function add($name, \Closure $code = null);

    public function output();

    public function input();

    public function sshClient();

    /**
     * @return null|Context
     */
    public function getCurrentTarget();

    public function getCurrentTargetHost();

    public function getCurrentTargetName();

    /**
     * @return null|integer
     */
    public function run();

    /**
     * @param Extension $extension
     * @return
     */
    public function addExtension(Extension $extension);

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
