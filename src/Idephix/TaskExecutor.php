<?php
namespace Idephix;

interface TaskExecutor
{
    const OUTPUT_NORMAL = 0;
    const OUTPUT_RAW    = 1;
    const OUTPUT_PLAIN  = 2;

    /**
     * @param $name
     * @return integer 0 success, 1 fail
     */
    public function execute($name);

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

    public function output();

    public function write($messages, $newline = false, $type = self::OUTPUT_NORMAL);

    public function writeln($messages, $type = self::OUTPUT_NORMAL);

    public function sshClient();
}
