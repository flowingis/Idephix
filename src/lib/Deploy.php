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
        if (!isset($this->targets[$env])) {
            throw new \InvalidArgumentException('Wrong environment "'.$env.'". Available ['.implode(', ', array_keys($this->targets)).']');
        }

        $this->sshClient->setHost(current($this->targets[$env]['hosts']));
        $this->localBaseFolder = rtrim($this->targets[$env]['localBaseFolder'], '/').'/';
        $this->remoteBaseFolder = rtrim($this->targets[$env]['remoteBaseFolder'], '/').'/';
        $this->releasesFolder = $this->remoteBaseFolder.'releases/';
        $this->hosts = $this->targets[$env]['hosts'];
        $this->rsyncExcludeFile = $this->targets[$env]['rsync_exclude_file'];
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
        $user = $this->sshParams['user'];
        $host = current($this->hosts);

        $dryFlag = $this->dryRun ? '--dry-run' : '';
        $exclude = $this->rsyncExcludeFile ? '--exclude-from='.$this->rsyncExcludeFile : '';

        exec("rsync -avcz --delete -e ssh $dryFlag $exclude $from $user@$host:$to", $out);
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
        $out = $this->remote("cd ".$this->getNextReleaseFolder()." && php app/console cache:clear --env=dev", $this->dryRun);
        $out .= "\n".$this->remote("cd ".$this->getNextReleaseFolder()." &&  php app/console cache:clear --env=prod --no-debug", $this->dryRun);

        return $out;
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

    public function runPhpUnit($params_string)
    {
        passthru('phpunit '.$params_string);
    }

    public function getLocalBaseFolder()
    {
        return $this->localBaseFolder;
    }
}