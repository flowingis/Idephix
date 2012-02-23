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
    private $sshParams;
    private $localBaseFolder;
    private $remoteBaseFolder;
    private $releasesFolder;
    private $hosts;
    private $dryRun = true;
    private $rsyncExcludeFile;

    public function __construct($sshClient, $target, $ssh_params) {
        $this->timestamp = date('YmdHis');

        $this->sshClient = $sshClient;
        $this->sshClient->setParams($ssh_params);
        $this->sshClient->setHost(current($target['hosts']));
        $this->sshClient->connect();

        $this->sshParams = $ssh_params;
        $this->localBaseFolder = rtrim($target['localBaseFolder'], '/').'/';
        $this->remoteBaseFolder = rtrim($target['remoteBaseFolder'], '/').'/';
        $this->releasesFolder = $this->remoteBaseFolder.'releases/';
        $this->hosts = $target['hosts'];
        $this->rsyncExcludeFile = $target['rsync_exclude_file'];
    }

    public function deploy() {
        try {
          deploy($this);
        } catch (\Exception $e) {
          $this->log("Error: ".$e->getMessage());
        }
    }

    public function getNextReleaseFolder() {
        return $this->releasesFolder.$this->timestamp;
    }

    public function getCurrentReleaseFolder() {
        return $this->remoteBaseFolder.'current';
    }

    public function remotePrepare() {
        if (strpos($this->remote('ls '.$this->getCurrentReleaseFolder()), 'No such file or directory') !== false) {
          throw new \Exception('You have to bootstrap your server first: '.current($this->hosts));
        }

        $cmd = "mkdir -p ".$this->getNextReleaseFolder();
        if ($this->dryRun) {
          $this->log($cmd);

          return true;
        }

        return $this->remote($cmd);
    }

    public function copyCode() {
        $out = $this->remoteCopyRecursive($this->remoteBaseFolder.'current/.', $this->getNextReleaseFolder());
        $out.= $this->rsync($this->localBaseFolder, ($this->dryRun) ? $this->getCurrentReleaseFolder() : $this->getNextReleaseFolder());

        return $out;
    }

    public function switchToTheNextRelease() {
        if ($this->dryRun) {
          $this->log("Switch to next release...");

          return true;
        }
        $this->remote("cd ".$this->remoteBaseFolder." && ln -s releases/".$this->timestamp." next && mv -fT next current");
    }

    public function rsync($from, $to) {
        $user = $this->sshParams['user'];
        $host = current($this->hosts);

        $dryFlag = $this->dryRun ? '--dry-run' : '';
        $exclude = $this->rsyncExcludeFile ? '--exclude-from='.$this->rsyncExcludeFile : '';

        exec("rsync -avz -e ssh $dryFlag $exclude $from $user@$host:$to", $out);
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
    public function assetic() {
      //php app/console assets:install --symlink web
    }

    public function remote($cmd) {
        return $this->sshClient->exec($cmd);
    }

    public function remoteCopyRecursive($from, $to) {
        $cmd = "cp -R '$from' '$to'";
        if ($this->dryRun) {
          $this->log($cmd);

          return true;
        }

        return $this->sshClient->exec($cmd);
    }

    public function bootstrap() {
        $bootstrapFolder = $this->releasesFolder.'bootstrap';
        $out = $this->remote("mkdir -p ".$bootstrapFolder);
        $out.= $this->remote("cd ".$this->remoteBaseFolder." && ln -s releases/bootstrap current");

        // @todo: share folder
        //ln -s ../shared/master/logs
        //ln -fs ../shared/web/imagine
        //ln -fs ../shared/web/uploads

        return $out;
    }

    private function log($message) {
      echo $message."\n";
    }
}