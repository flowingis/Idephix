<?php

namespace Idephix\Extension\Deploy\Strategy;

use Idephix\Idephix;
use Idephix\Config\Config;

class Copy implements DeployStrategyInterface
{
    protected $idx;
    protected $conf;
    protected $rsyncExcludeFile;
    protected $rsyncIncludeFile;


    public function __construct(Idephix $idx, Config $target)
    {
        $this->idx = $idx;

        $this->target = $target;
        $this->rsyncExcludeFile = $target->get('deploy.rsync_exclude_file');
        $this->rsyncIncludeFile = $target->get('deploy.rsync_include_file');
    }

    /**
     * Main deploy method
     * @return string commands output
     */
    public function deploy()
    {
        $this->idx->output->writeln("Copy code to the next release dir");
        $this->remoteCopyRecursive(
            $this->target->get('deploy.current_release_dir').'/.',
            $this->target->get('deploy.next_release_dir'));
        $out = $this->idx->sshClient->getLastOutput();

        $this->idx->output->writeln("Sync code to the next release");
        $this->rsync(
            $this->target->get('deploy.local_base_folder'),
            ($this->target->get('deploy.dry_run')) ? $this->target->get('deploy.current_release_dir').'/' : $this->target->get('deploy.next_release_dir'));
        $out .= $this->idx->sshClient->getLastOutput();

        return $out;
    }

    /**
     * exec rsync from local dir to remote target dir
     * @param string $from local source path
     * @param string $to   remote target path
     *
     * @return int command return status
     */
    public function rsync($from, $to)
    {
        $user = $this->idx->sshClient->getUser();
        $host = $this->idx->sshClient->getHost();

        $dryFlag = $this->target->get('deploy.dry_run') ? '--dry-run' : '';
        $exclude = $this->rsyncExcludeFile ? '--exclude-from='.$this->rsyncExcludeFile : '';
        $include = $this->rsyncIncludeFile ? '--include-from='.$this->rsyncIncludeFile : '';
        $sshCmd = "-e 'ssh";
        $sshCmd.= $this->idx->sshClient->getPort() ? " -p ".$this->idx->sshClient->getPort() : "";
        $sshCmd.= "'";

        return $this->idx->local("rsync -rlpDvcz --delete $sshCmd $dryFlag $exclude $include $from $user@$host:$to");
    }

    public function remoteCopyRecursive($from, $to)
    {
        return $this->idx->remote(
            sprintf("cp -pPR %s %s", escapeshellarg($from), escapeshellarg($to)),
            $this->target->get('deploy.dry_run'));
    }


}