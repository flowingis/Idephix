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
    private $useAssetic = true;
    private $strategy;
    private $sharedFolders = array();
    private $sharedSymlinks = array();
    private $symfonyEnv;

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

        $this->symfonyEnv = $target->get('symfony_env', 'dev');
        $this->hasToMigrate = $target->get('deploy.migrations', false);
        $this->useAssetic = $target->get('deploy.assetic', true);
        $this->localBaseFolder  = $target->getFixedPath('deploy.local_base_dir');
        $this->remoteBaseFolder = $target->getFixedPath('deploy.remote_base_dir');
        $this->releasesFolder   = $this->remoteBaseFolder.'releases/';
        $this->sharedFolders    = $target->get('deploy.shared_folders', array());
        $this->sharedSymlinks   = $target->get('deploy.shared_symlinks', null);
        
        if ($this->sharedFolders && is_null($this->sharedSymlinks)) {
            throw new \Exception(sprintf('In "%s" target, deploy.shared_folders parameter was set but deploy.shared_symlinks was not. You must define deploy.shared_symlinks too.', $this->idx->getCurrentTargetName()));
        }

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

    /**
     * @param boolean $dryRun
     */
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

    /**
     * Check if the current remote host is already bootstrapped
     *
     * @return boolean true if the host is ready, false otherwise
     */
    public function isRemoteReady()
    {
        try {

            $this->idx->remote('ls '.$this->getCurrentReleaseFolder());
            $this->log("Host ready ".$this->sshClient->getHost());

            return true;

        } catch (\Exception $e) {

            $this->log(sprintf("Host %s NOT ready", $this->sshClient->getHost()));

            return false;

        }

    }

    /**
     * Create the next release folder
     *
     * @return string the output of the creation command
     */
    public function remotePrepare()
    {

        $cmd = "mkdir -p ".$this->getNextReleaseFolder();

        return $this->idx->remote($cmd, $this->dryRun);

    }

    /**
     * Update the "current" symlink to the next release folder
     */
    public function switchToTheNextRelease()
    {
        $this->log("Switch to next release...");
        $this->idx->remote("cd ".$this->remoteBaseFolder." && ln -s releases/".$this->getNextReleaseName()." next && mv -fT next current", $this->dryRun);
    }

    /**
     * @param string $path
     * @return boolean
     */
    public function remoteFileExists($path)
    {
        try {
            $this->idx->remote("[ -e '$path' ]", $this->dryRun);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Link shared files and folder to the next release
     */
    public function remoteCreateSymlinks()
    {
        $this->log("Updating symlink for shared folder ..");

        foreach ($this->sharedSymlinks as $file) {

            $fullPathSharedFile        = $this->remoteBaseFolder.'shared/'.$file;
            $fullPathReleaseSharedFile = $this->remoteBaseFolder.'releases/'.$this->getNextReleaseName()."/".$file;

            $this->log("Creating shared symlink for ".$fullPathReleaseSharedFile." ...");

            if (
                    !$this->remoteFileExists($fullPathSharedFile)
                    && $this->remoteFileExists($fullPathReleaseSharedFile)
                ) {
                
                $this->idx->remote(
                        sprintf(
                            "cp %s %s",
                            $fullPathReleaseSharedFile,
                            $fullPathSharedFile
                        ),
                        $this->dryRun);
            }

            if ($this->remoteFileExists($fullPathReleaseSharedFile)) {
                try {
                    $this->idx->remote(
                        sprintf(
                            "unlink %s || rmdir %s || rm -R %s",
                            $fullPathReleaseSharedFile,
                            $fullPathReleaseSharedFile,
                            $fullPathReleaseSharedFile
                        ),
                        $this->dryRun
                    );
                } catch (\Exception $e) {
                    throw new \Exception(
                        sprintf(
                            'Unable to link shared file "%s". Destination file or directory exists.',
                            $fullPathReleaseSharedFile
                        )
                    );
                }
            }

            $this->idx->remote('ln -nfs '.$fullPathSharedFile. ' '.$fullPathReleaseSharedFile, $this->dryRun);
        }
    }

    /**
     * Run assets:install and assetic:dump on remote host
     *
     * @param boolean $current if true run commands on current release folder, on next release folder otherwise
     */
    public function assetic($current = true)
    {
        $folder = $current ? $this->getCurrentReleaseFolder() : $this->getNextReleaseFolder();
        $this->log("Asset and assetic stuff...");
        $this->idx->remote('cd '.$folder." && php app/console assets:install --symlink web --env=$this->symfonyEnv", $this->dryRun);
        $this->idx->remote('cd '.$folder." && php app/console assetic:dump --env=$this->symfonyEnv --no-debug", $this->dryRun);
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
     * Symfony app/console cache:clear
     */
    public function cacheClear()
    {
        return $this->idx->remote('cd '.$this->getNextReleaseFolder()." && rm -Rf app/cache/*", $this->dryRun);
        return $this->idx->remote('cd '.$this->getNextReleaseFolder()." && ./app/console cache:clear --env=$this->symfonyEnv --no-debug", $this->dryRun);
    }

    /**
     * Symfony app/console doctrine:migration:migrate
     */
    public function doctrineMigrate()
    {
        return $this->idx->remote('cd '.$this->getNextReleaseFolder()." && ./app/console doctrine:migration:migrate --env=$this->symfonyEnv", $this->dryRun);
    }

    /**
     * Create the basic structure folder for deploy with releases
     * @return string the output of remote commands executed
     */
    public function bootstrap()
    {
        $this->log("Boostrapping environment ...");

        $bootstrapFolder = $this->releasesFolder.'bootstrap';
        $this->idx->remote("mkdir -p ".$bootstrapFolder);
        $out = $this->sshClient->getLastOutput();
        $this->idx->remote("cd ".$this->remoteBaseFolder." && ln -s releases/bootstrap current");
        $out .= $this->sshClient->getLastOutput();

        $this->updateSharedFolders();

        return $out;
    }
    
    private function updateSharedFolders()
    {
        $this->log("Creating shared folders...");

        foreach ($this->sharedFolders as $folder) {
            $this->log("Creating shared folder ".$folder." ...");
            $this->idx->remote('mkdir -p '.$this->remoteBaseFolder.'shared/'.$folder);
        }
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

    public function useAssetic()
    {
        return $this->useAssetic;
    }
    
    public function deploySF2Copy($go, $releasesToKeep = 6, $automaticBootstrap = true)
    {

        $this->setDryRun(!$go);

        $this->setUpEnvironment();

        if (!$this->isRemoteReady()) {
            if ($automaticBootstrap) {
                $this->bootstrap();
            } else {
                throw new \Exception("Remote host not ready for deploy");
            }
        }

        $this->remotePrepare();

        $this->strategy->deploy();

        $this->updateSharedFolders();
        $this->remoteCreateSymlinks();

        if ($this->hasToMigrate()) {
            $this->doctrineMigrate();
        }

        $this->cacheClear();
        $this->switchToTheNextRelease();
        if ($this->useAssetic()) {
            $this->assetic();
        }
        $this->deleteOldReleases($releasesToKeep);

    }
   
    public function getStrategy()
    {
      return $this->strategy;
    }
}
