<?php

namespace Ideato\Deploy;

/**
 * Description of Deploy
 *
 * @author kea
 */
class Deploy
{
    private $sshClient;
    private $localBaseFolder;
    private $remoteBaseFolder;
    private $releasesFolder;
    private $hosts;
    private $dryRun = true;
    private $rsyncExcludeFile;
    private $rsyncIncludeFile;
    private $timestamp;
    private $targets;

    public function __construct($sshClient, $targets)
    {
        $this->timestamp = date('YmdHis');
        $this->sshClient = $sshClient;
        $this->targets = $targets;
    }

    public function setEnvironment($env)
    {
        if (!isset($this->targets[$env])) {
            throw new \InvalidArgumentException('Wrong environment "'.$env.'". Available ['.implode(', ', array_keys($this->targets)).']');
        }

        $this->sshClient->setHost(current($this->targets[$env]['hosts']));
        $this->localBaseFolder = rtrim($this->targets[$env]['localBaseFolder'], '/').'/';
        $this->remoteBaseFolder = rtrim($this->targets[$env]['remoteBaseFolder'], '/').'/';
        $this->releasesFolder = $this->remoteBaseFolder.'releases/';
        $this->hosts = $this->targets[$env]['hosts'];
        $this->rsyncExcludeFile = empty($this->targets[$env]['rsync_exclude_file']) ? null : $this->targets[$env]['rsync_exclude_file'];
        $this->rsyncIncludeFile = empty($this->targets[$env]['rsync_include_file']) ? null : $this->targets[$env]['rsync_include_file'];
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

    public function remotePrepare()
    {
        if (strpos($this->remote('ls '.$this->getCurrentReleaseFolder()), 'No such file or directory') !== false) {
            throw new \Exception('You have to bootstrap your server first: '.current($this->hosts));
        }
        $this->log("Bootstrap: OK");
        $cmd = "mkdir -p ".$this->getNextReleaseFolder();

        return $this->remote($cmd, $this->dryRun);
    }

    public function copyCode()
    {
        $this->log("Remote: copy code to the next release");
        $out = $this->remoteCopyRecursive($this->remoteBaseFolder.'current/.', $this->getNextReleaseFolder());
        $this->log("Remote: sync code to the next release");
        $out.= $this->rsync($this->localBaseFolder, ($this->dryRun) ? $this->getCurrentReleaseFolder().'/' : $this->getNextReleaseFolder());

        return $out;
    }

    public function switchToTheNextRelease()
    {
        $this->log("Switch to next release...");
        $this->remote("cd ".$this->remoteBaseFolder." && ln -s releases/".$this->timestamp." next && mv -fT next current", $this->dryRun);
    }

    public function rsync($from, $to)
    {
        $user = $this->sshClient->getUser();
        $host = current($this->hosts);

        $dryFlag = $this->dryRun ? '--dry-run' : '';
        $exclude = $this->rsyncExcludeFile ? '--exclude-from='.$this->rsyncExcludeFile : '';
        $include = $this->rsyncIncludeFile ? '--include-from='.$this->rsyncIncludeFile : '';
        $sshCmd = "-e 'ssh";
        $sshCmd.= $this->sshClient->getPort() ? " -p ".$this->sshClient->getPort() : "";
        $sshCmd.= "'";

        exec("rsync -rlpDvcz --delete $sshCmd $dryFlag $exclude $include $from $user@$host:$to", $out);
        $this->log(implode("\n", $out));

        return $out;
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
     * @todo
     */
    public function assetic($current = true)
    {
        $folder = $current ? $this->getCurrentReleaseFolder() : $this->getNextReleaseFolder();
        $this->log("Asset and assetic stuff...");
        $this->remote('cd '.$folder.' && php app/console assets:install --symlink web', $this->dryRun);
        $this->remote('cd '.$folder.' && php app/console assetic:dump --env=prod', $this->dryRun);
    }

    public function remote($cmd, $dryRun = false)
    {
        $this->sshClient->connect();
        $this->log('Remote: '.$cmd);
        if (!$dryRun) {
            return $this->sshClient->exec($cmd);
        }
    }

    public function remoteCopyRecursive($from, $to)
    {
        return $this->remote("cp -pR '$from' '$to'", $this->dryRun);
    }

    public function updateSchema()
    {
        return $this->remote("cd ".$this->getNextReleaseFolder()." && php app/console doctrine:schema:update --force", $this->dryRun);
    }

    /**
     * @todo sudo?
     */
    public function deleteOldReleases($releasesToKeep)
    {
        return $this->remote("cd ".$this->releasesFolder." && ls | sort | head -n -".$releasesToKeep." | xargs rm -Rf", $this->dryRun);
    }

    /**
     * @todo sudo?
     */
    public function cacheClear()
    {
        return $this->remote('cd '.$this->getNextReleaseFolder().' && rm -Rf app/cache/*', $this->dryRun);
    }

    public function bootstrap()
    {
        $bootstrapFolder = $this->releasesFolder.'bootstrap';
        $out = $this->remote("mkdir -p ".$bootstrapFolder);
        $out.= $this->remote("cd ".$this->remoteBaseFolder." && ln -s releases/bootstrap current");

        // @todo: share folder
        //ln -s ../shared/master/logs
        //ln -fs ../shared/web/imagine
        //ln -fs ../shared/web/uploads

        return $out;
    }

    private function log($message)
    {
        echo $message."\n";
    }

    public function getLocalBaseFolder()
    {
        return $this->localBaseFolder;
    }
}
