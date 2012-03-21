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

    public function __construct($sshClient, $targets, $ssh_params)
    {
        $this->timestamp = date('YmdHis');

        $this->sshClient = $sshClient;
        $this->sshClient->setParams($ssh_params);
        $this->sshParams = $ssh_params;
        $this->targets = $targets;
    }

    public function setEnvironment($env)
    {
        $this->sshClient->setHost(current($this->targets[$env]['hosts']));
        $this->localBaseFolder = rtrim($this->targets[$env]['localBaseFolder'], '/').'/';
        $this->remoteBaseFolder = rtrim($this->targets[$env]['remoteBaseFolder'], '/').'/';
        $this->releasesFolder = $this->remoteBaseFolder.'releases/';
        $this->hosts = $this->targets[$env]['hosts'];
        $this->rsyncExcludeFile = $this->targets[$env]['rsync_exclude_file'];
    }

    public function callback($argv)
    {
        try {
            $callback = \array_shift($argv);
            \array_unshift($argv, $this);
            \call_user_func_array($callback, $argv);
        } catch (\Exception $e) {
            $this->log("Error: ".$e->getMessage());
        }
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
        $out.= $this->rsync($this->localBaseFolder, ($this->dryRun) ? $this->getCurrentReleaseFolder() : $this->getNextReleaseFolder());

        return $out;
    }

    public function switchToTheNextRelease()
    {
        $this->log("Switch to next release...");
        $this->remote("cd ".$this->remoteBaseFolder." && ln -s releases/".$this->timestamp." next && mv -fT next current", $this->dryRun);
    }

    public function rsync($from, $to)
    {
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
    public function assetic()
    {
        $this->log("Asset and assetic stuff...");
        $this->remote('cd '.$this->getNextReleaseFolder().' && php app/console assets:install --symlink web', $this->dryRun);
        $this->remote('cd '.$this->getNextReleaseFolder().' && php app/console assetic:dump --env=prod', $this->dryRun);
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
        return $this->remote("cp -R '$from' '$to'", $this->dryRun);
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

    public function runPhpUnit()
    {
        passthru('phpunit -c app/ --stderr');
    }

    public function getLocalBaseFolder()
    {
        return $this->localBaseFolder;
    }
}