<?php

namespace Idephix\Extension\Deploy;

use Idephix\Idephix;
use Idephix\Extension\IdephixAwareInterface;

/**
 * Basic Deploy class
 *
 * @author kea
 */
class Deploy implements IdephixAwareInterface
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
    private $hasToMigrate = false;

    public function __construct()
    {
        $this->timestamp = date('YmdHis');
    }

    public function setIdephix(Idephix $idx)
    {
        $this->sshClient = $idx->sshClient;
        $this->idx = $idx;
    }

    /**
     * Add trailing slash to the path if it is omitted
     * @param string $path
     *
     * @return string fixed path
     */
    private function fixPath($path)
    {
        return rtrim($path, '/').'/';
    }

    public function setUpEnvironment()
    {
        if (null === $this->idx->getCurrentTargetName()) {
            throw new \Exception("You must specify an environment [--env]");
        }

        $target = $this->idx->getCurrentTarget();

        if (!$target->get('deploy.remote_base_dir', false)) {
            throw new \Exception("No deploy parameters found. Check you configuration.");
        }

        $this->hasToMigrate = $target->get('deploy.migrations', false);
        $this->localBaseFolder  = $this->fixPath($target->get('deploy.local_base_dir'));
        $this->remoteBaseFolder = $this->fixPath($target->get('deploy.remote_base_dir'));
        $this->releasesFolder   = $this->fixPath($this->remoteBaseFolder.'releases');
        $this->rsyncExcludeFile = $target->get('deploy.rsync_exclude_file', null);
        $this->rsyncIncludeFile = $target->get('deploy.rsync_include_file', null);
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
        try {
            $this->idx->remote('ls '.$this->getCurrentReleaseFolder());
        } catch (\Exception $e) {
            if (!$forceBootstrap) {
                throw new \Exception('You have to bootstrap your server first: '.$this->sshClient->getHost());
            }

            $this->bootstrap();
        }

        $this->log("Bootstrap: OK");
        $cmd = "mkdir -p ".$this->getNextReleaseFolder();

        return $this->idx->remote($cmd, $this->dryRun);
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

    /**
     * exec rsync from local dir to remote target dir
     * @param string $from local source path
     * @param string $to   remote target path
     *
     * @return int command return status
     */
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
    public function remoteLinkSharedFolders()
    {
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
     * !UNSAFE FOR PRODUCTION ENVIRONMENT!
     * @param string $env the environment
     *
     * @return string output of the remote command
     */
    public function updateSchema($env = 'dev')
    {
        return $this->idx->remote(
            "cd ".$this->getNextReleaseFolder()." && php app/console doctrine:schema:update --force --env=".$env,
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
        return $this->idx->remote('cd '.$this->getNextReleaseFolder().' && ./app/console cache:clear --env=prod --no-debug && ./app/console cache:warmup', $this->dryRun);
    }

    /**
     * @todo sudo?
     */
    public function doctrineMigrate()
    {
        return $this->idx->remote('cd '.$this->getNextReleaseFolder().' && ./app/console doctrine:migration:migrate', $this->dryRun);
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

    public function hasToMigrate()
    {
        return $this->hasToMigrate;
    }

    public function deploySF2Copy($go, $releasesToKeep = 6, $automaticBootstrap = true)
    {
        $this->idx->setUpEnvironment();
        $this->idx->setDryRun(!$go);
        $this->idx->remotePrepare($automaticBootstrap);
        $this->idx->copyCode();
        $this->idx->remoteLinkSharedFolders();
        if ($this->hasToMigrate()) {
            $this->idx->doctrineMigrate();
        }
        $this->idx->cacheClear();
        $this->idx->switchToTheNextRelease();
        $this->idx->assetic();
        $this->idx->deleteOldReleases($releasesToKeep);
    }
}
