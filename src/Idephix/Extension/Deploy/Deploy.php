<?php

namespace Idephix\Extension\Deploy;

use Idephix\IdephixInterface;
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
    private $timestamp;
    private $hasToMigrate = false;
    private $strategy;

    public function __construct()
    {
        $this->timestamp = date('YmdHis');
    }

    public function setIdephix(IdephixInterface $idx)
    {
        $this->sshClient = $idx->sshClient;
        $this->idx = $idx;
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
        $this->localBaseFolder  = $target->getFixedPath('deploy.local_base_dir');
        $this->remoteBaseFolder = $target->getFixedPath('deploy.remote_base_dir');
        $this->releasesFolder   = $this->remoteBaseFolder.'releases/';
        $target->set('deploy.releases_dir', $this->releasesFolder);
        $target->set('deploy.current_release_dir', $this->getCurrentReleaseFolder());
        $target->set('deploy.next_release_dir', $this->getNextReleaseFolder());
        $target->set('deploy.dry_run', $this->dryRun);

        $strategyClass = 'Idephix\\Extension\\Deploy\\Strategy\\'.$target->get('deploy.strategy', 'Copy');

        if (!class_exists($strategyClass)) {
            throw new \Exception(sprintf("No deploy strategy %s found. Check you configuration.", $strategyClass));
        }

        $this->strategy = new $strategyClass($this->idx, $target);
    }

    public function setDryRun($dryRun)
    {
        $this->dryRun = $dryRun;
    }

    public function getNextReleaseName()
    {
        return $this->timestamp;
    }

    public function getNextReleaseFolder()
    {
        return $this->releasesFolder.$this->getNextReleaseName();
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

    public function switchToTheNextRelease()
    {
        $this->log("Switch to next release...");
        $this->idx->remote("cd ".$this->remoteBaseFolder." && ln -s releases/".$this->getNextReleaseName()." next && mv -fT next current", $this->dryRun);
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
        $this->idx->remote('cd '.$folder.' && php app/console assetic:dump --env=prod --no-debug', $this->dryRun);
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
        return $this->idx->remote('cd '.$this->getNextReleaseFolder().' && ./app/console cache:clear --env=prod --no-debug', $this->dryRun);
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
        // $this->idx->remote('mkdir -p '.$this->releaseFolder.'shared');

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
        $this->setDryRun(!$go);
        $this->setUpEnvironment();
        $this->remotePrepare($automaticBootstrap);
        $this->strategy->deploy();
        $this->remoteLinkSharedFolders();
        if ($this->hasToMigrate()) {
            $this->doctrineMigrate();
        }
        $this->cacheClear();
        $this->switchToTheNextRelease();
        $this->assetic();
        $this->deleteOldReleases($releasesToKeep);
    }
}
