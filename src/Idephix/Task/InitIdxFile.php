<?php
namespace Idephix\Task;

use Idephix\Extension\IdephixAwareInterface;
use Idephix\IdephixInterface;
use Idephix\Task\Parameter\Collection;
use Idephix\Task\Parameter\UserDefinedCollection;

class InitIdxFile implements Task, IdephixAwareInterface
{
    private $idx;
    private $baseDir;

    public function __construct($baseDir = '.')
    {
        $this->baseDir = $baseDir;
    }

    public function name()
    {
        return 'initFile';
    }

    public function description()
    {
        return 'Init idx configurations and tasks file';
    }

    public function parameters()
    {
        return Collection::dry();
    }

    public function userDefinedParameters()
    {
        return new UserDefinedCollection($this->parameters());
    }

    public function code()
    {
        return array($this, 'initFile');
    }

    /**
     * Based by composer self-update
     */
    public function initFile()
    {
        $this->initIdxFile();
        $this->initIdxRc();
    }

    private function initIdxRc()
    {
        $data = <<<'DEFAULT'
<?php

$targets = array(
    'prod' => array(
        'hosts' => array('127.0.0.1'),
        'ssh_params' => $sshParams,
        'deploy' => array(
            'local_base_dir' => $localBaseDir,
            'remote_base_dir' => "/var/www/myfantasticserver/",
            // 'rsync_exclude_file' => 'rsync_exclude.txt'
            // 'rsync_include_file' => 'rsync_include.txt'
            // 'migrations' => true
            // 'strategy' => 'Copy'
        ),
    ),
);
return \Idephix\Config::fromArray(
    array(
        'targets' => $targets, 
        'sshClient' => new \Idephix\SSH\SshClient(new \Idephix\SSH\CLISshProxy())
    )
);
DEFAULT;

        $this->writeFile('idxrc.php', $data);
    }

    private function initIdxFile()
    {
        $data = <<<'DEFAULT'
<?php

function sf2Deploy($idx, $go = false)
{
    if (!$go) {
        echo "\nDry Run...\n";
    }
    $deploy = new \Idephix\Extension\Deploy\Deploy();
    $deploy->setIdephix($idx);

    $deploy->deploySF2Copy($go);
}

/**
 * Build your Symfony project after you have downloaded it for the first time
 */
function buildFromscratch($idx)
{
    if (!file_exists(__DIR__ . "/composer.phar")) {
        $idx->output->writeln("Downloading composer.phar ...");
        $idx->local("curl -sS https://getcomposer.org/installer | php");
    }

    $idx->local("php composer.phar install");
    $idx->local("./app/console doctrine:schema:update --force");
    $idx->runTask('asset:install');
    $idx->local("./app/console cache:clear --env=dev");
    $idx->local("./app/console cache:clear --env=test");
    $idx->runTask('test:run');
}

/**
 * Symfony2 installing assets and running assetic command
 */
function assetInstall($idx)
{
    $idx->local("app/console assets:install web");
    $idx->local("app/console assetic:dump");
}
/**
 * run phpunit tests
 */
function testRun($idx)
{
    $phpunit = new \Idephix\Extension\PHPUnit\PHPUnit();
    $phpunit->setIdephix($idx);

    $phpunit->runPhpUnit('-c app/');
}

DEFAULT;

        $this->writeFile('idxfile.php', $data);
    }

    private function writeFile($filename, $data)
    {
        $idxFile = $this->baseDir . DIRECTORY_SEPARATOR . $filename;
        if (file_exists($idxFile)) {
            $this->idx->output->writeln("<error>An {$filename} already exists, generation skipped.</error>");

            return;
        }

        $this->idx->output->writeln("Creating basic {$filename} file...");

        if (!is_writable($this->baseDir) || false === file_put_contents($idxFile, $data)) {
            throw new \Exception("Cannot write {$filename}, check your permission configuration.");
        }

        $this->idx->output->writeln("{$filename} file created.");
    }

    /**
     * @param IdephixInterface $idx
     * @return void
     */
    public function setIdephix(IdephixInterface $idx)
    {
        $this->idx = $idx;
    }
}
