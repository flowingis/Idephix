<?php

namespace Idephix\Extension\Deploy\Strategy;

use Idephix\Context;
use Idephix\IdephixInterface;
use Idephix\SSH\SshClient;
use Symfony\Component\Console\Output\Output;

class Copy implements DeployStrategyInterface
{
    protected $idx;
    protected $conf;
    protected $rsyncExcludeFile;
    protected $rsyncIncludeFile;
    protected $target;
    /**
     * @var Output
     */
    protected $output;
    /**
     * @var SshClient
     */
    protected $sshClient;

    public function __construct(IdephixInterface $idx, Context $currentContext)
    {
        $this->idx = $idx;
        if (!$idx->output() instanceof Output) {
            throw new \InvalidArgumentException('Idx output should be a writable console');
        }
        $this->output = $idx->output();

        if (!$idx->sshClient() instanceof SshClient) {
            throw new \InvalidArgumentException('Idx should have an SSH client connected');
        }
        $this->sshClient = $idx->sshClient();

        $this->target = $currentContext;
        $this->rsyncExcludeFile = $currentContext->get('deploy.rsync_exclude_file');
        $this->rsyncIncludeFile = $currentContext->get('deploy.rsync_include_file');
    }

    /**
     * Main deploy method
     * @return string commands output
     */
    public function deploy()
    {
        $this->output->writeln('Copy code to the next release dir');
        $this->remoteCopyRecursive(
            $this->target->get('deploy.current_release_dir').'/.',
            $this->target->get('deploy.next_release_dir')
        );
        $out = $this->sshClient->getLastOutput();

        $this->output->writeln('Sync code to the next release');
        $this->rsync(
            $this->target->getAsPath('deploy.local_base_dir'),
            ($this->target->get('deploy.dry_run')) ? $this->target->get('deploy.current_release_dir').'/' : $this->target->get('deploy.next_release_dir')
        );
        $out .= $this->sshClient->getLastOutput();

        return $out;
    }

    /**
     * exec rsync from local dir to remote target dir
     * @param string $from local source path
     * @param string $to   remote target path
     *
     * @return string command return status
     */
    public function rsync($from, $to)
    {
        $user = $this->sshClient->getUser();
        $host = $this->sshClient->getHost();

        $dryFlag = $this->target->get('deploy.dry_run') ? '--dry-run' : '';
        $exclude = $this->rsyncExcludeFile ? '--exclude-from='.$this->rsyncExcludeFile : '';
        $include = $this->rsyncIncludeFile ? '--include-from='.$this->rsyncIncludeFile : '';
        $sshCmd = "-e 'ssh";
        $sshCmd.= $this->sshClient->getPort() ? ' -p '.$this->sshClient->getPort() : '';
        $sshCmd.= "'";

        return $this->idx->local("rsync -rlpDvcz --delete $sshCmd $dryFlag $exclude $include $from $user@$host:$to");
    }

    /**
     * @param string $from
     * @param string $to
     */
    public function remoteCopyRecursive($from, $to)
    {
        return $this->idx->remote(
            sprintf('cp -pPR %s %s', escapeshellarg($from), escapeshellarg($to)),
            $this->target->get('deploy.dry_run')
        );
    }
}
