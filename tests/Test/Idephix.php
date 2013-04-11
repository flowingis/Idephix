<?php

namespace Idephix\Tests\Test;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Process\Process;
use Idephix\SSH\SshClient;
use Idephix\Extension\IdephixAwareInterface;
use Idephix\Extension\SelfUpdate\SelfUpdate;
use Idephix\Config\Config;

class Idephix extends \Idephix\Idephix
{
    public function initFirstHost()
    {
        try {
            $this->buildEnvironment($this->input);
        } catch (\Exception $e) {
            $this->output->writeln('<error>'.$e->getMessage().'</error>');

            return;
        }

        $hosts = $this->hasTarget() ? $this->currentTarget->get('hosts') : array(null);

        $host = current($hosts);
        $this->currentHost = $host;
        $this->openRemoteConnection($host);
    }

    public function remote($cmd, $dryRun = false)
    {
        $this->output->writeln('<info>Remote</info>: '.$cmd);

        return $cmd;
    }

    public function local($cmd)
    {
        $this->output->writeln('<info>Local</info>: '.$cmd);

        return $cmd;
    }
}
