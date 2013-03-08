<?php

namespace Ideato\Deploy;

use Ideato\Idephix;

/**
 * Basic Deploy class
 *
 * @author kea
 */
class Deploy
{
    private $idx;
    private $sshClient;
    private $localBaseFolder;
    private $remoteBaseFolder;
    private $releasesFolder;
    private $dryRun = true;
    private $rsyncExcludeFile;
    private $rsyncIncludeFile;
    private $timestamp;

    public function __construct(Idephix $idx)
    {
        $this->timestamp = date('YmdHis');
        $this->sshClient = $idx->sshClient;
        $this->idx = $idx;
    }

    public function setUpEnvironment()
    {
        if (null === $this->idx->getCurrentTargetName()) {
            throw new \Exception("You must specify an environment [--env]");
        }

        $target = $this->idx->getCurrentTarget();
        $this->localBaseFolder = rtrim($target['local_base_folder'], '/').'/';
        $this->remoteBaseFolder = rtrim($target['remote_base_folder'], '/').'/';
        $this->releasesFolder = $this->remoteBaseFolder.'releases/';
        $this->rsyncExcludeFile = empty($target['rsync_exclude_file']) ? null : $target['rsync_exclude_file'];
        $this->rsyncIncludeFile = empty($target['rsync_include_file']) ? null : $target['rsync_include_file'];
    }

    public function setDryRun($dryRun)
    {
        $this->dryRun = $dryRun;
    }

    public function getNextReleaseFolder()
    {
        return $this->releasesFolder.$this->timestamp;
    }

    public function getCurrentReleaseFolder()
    {
        return $this->remoteBaseFolder.'current';
    }

    public function getRemoteBaseFolder()
    {
        return $this->remoteBaseFolder;
    }

    public function getLocalBaseFolder()
    {
        return $this->localBaseFolder;
    }

    public function remotePrepare($forceBootstrap = false)
    {
        $isBootstrapDone = 0 == $this->idx->remote('ls '.$this->getCurrentReleaseFolder());
        if (!$isBootstrapDone) {
            if (!$forceBootstrap) {
                throw new \Exception('You have to bootstrap your server first: '.$this->sshClient->getHost());
            }

            $this->bootstrap();
        }
        $this->log("Bootstrap: OK");
        $cmd = "mkdir -p ".$this->getNextReleaseFolder();

        return 0 == $this->idx->remote($cmd, $this->dryRun);
    }

    public function copyCode()
    {
        $this->log("Remote: copy code to the next release");
        $this->remoteCopyRecursive($this->remoteBaseFolder.'current/.', $this->getNextReleaseFolder());
        $out = $this->sshClient->getLastOutput();
        $this->log("Remote: sync code to the next release");
        $this->rsync($this->localBaseFolder, ($this->dryRun) ? $this->getCurrentReleaseFolder().'/' : $this->getNextReleaseFolder());
        $out .= $this->sshClient->getLastOutput();

        return $out;
    }

    public function switchToTheNextRelease()
    {
        $this->log("Switch to next release...");
        $this->idx->remote("cd ".$this->remoteBaseFolder." && ln -s releases/".$this->timestamp." next && mv -fT next current", $this->dryRun);
    }

    public function rsync($from, $to)
    {
        $user = $this->sshClient->getUser();
        $host = $this->sshClient->getHost();

        $dryFlag = $this->dryRun ? '--dry-run' : '';
        $exclude = $this->rsyncExcludeFile ? '--exclude-from='.$this->rsyncExcludeFile : '';
        $include = $this->rsyncIncludeFile ? '--include-from='.$this->rsyncIncludeFile : '';
        $sshCmd = "-e 'ssh";
        $sshCmd.= $this->sshClient->getPort() ? " -p ".$this->sshClient->getPort() : "";
        $sshCmd.= "'";

        return $this->idx->local("rsync -rlpDvcz --delete $sshCmd $dryFlag $exclude $include $from $user@$host:$to");
    }

    /**
     * @todo
     */
    public function remoteLinkSharedFolders() {
      //ln -s ../shared/master/logs
      //ln -fs ../shared/web/imagine
      //ln -fs ../shared/web/uploads
    }

    /**
     * @todo env?
     */
    public function assetic($current = true)
    {
        $folder = $current ? $this->getCurrentReleaseFolder() : $this->getNextReleaseFolder();
        $this->log("Asset and assetic stuff...");
        $this->idx->remote('cd '.$folder.' && php app/console assets:install --symlink web', $this->dryRun);
        $this->idx->remote('cd '.$folder.' && php app/console assetic:dump --env=prod', $this->dryRun);
    }

    public function remoteCopyRecursive($from, $to)
    {
        return $this->idx->remote(
            sprintf("cp -pR %s %s", escapeshellarg($from), escapeshellarg($to)),
            $this->dryRun);
    }

    /**
     * Execute the doctrine:schema:update sf2 console command
     * @param string $env the environment
     *
     * @return string output of the remote command
     */
    public function updateSchema($env = 'dev')
    {
        return $this->idx->remote(
            "cd ".$this->getNextReleaseFolder()." && php app/console doctrine:schema:update --force",
            $this->dryRun
        );
    }

    /**
     * @param int $releasesToKeep how many releases you want to keep
     *
     * @todo sudo?
     */
    public function deleteOldReleases($releasesToKeep)
    {
        return $this->idx->remote(
            sprintf(
                "cd %s && ls | sort | head -n -%d | xargs rm -Rf",
                escapeshellarg($this->releasesFolder),
                $releasesToKeep
            ),
            $this->dryRun
        );
    }

    /**
     * @todo sudo?
     */
    public function cacheClear()
    {
        return $this->idx->remote('cd '.$this->getNextReleaseFolder().' && rm -Rf app/cache/*', $this->dryRun);
    }

    /**
     * Create the basic structure folder for deploy with releases
     * @return string the output of remote commands executed
     */
    public function bootstrap()
    {
        $bootstrapFolder = $this->releasesFolder.'bootstrap';
        $this->idx->remote("mkdir -p ".$bootstrapFolder);
        $out = $this->sshClient->getLastOutput();
        $this->idx->remote("cd ".$this->remoteBaseFolder." && ln -s releases/bootstrap current");
        $out .= $this->sshClient->getLastOutput();

        // @todo: share folder
        //ln -s ../shared/master/logs
        //ln -fs ../shared/web/imagine
        //ln -fs ../shared/web/uploads

        return $out;
    }

    /**
     * Proxy to idephix output->writeln method
     * @param string $message
     */
    private function log($message)
    {
        $this->idx->output->writeln($message);
    }
}
