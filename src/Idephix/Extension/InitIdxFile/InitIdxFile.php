<?php

namespace Idephix\Extension\InitIdxFile;

use Idephix\Idephix;
use Idephix\Extension\IdephixAwareInterface;
use Idephix\IdephixInterface;

class InitIdxFile implements IdephixAwareInterface
{
    private $idx;

    private $baseDir;

    public function __construct($baseDir = '.')
    {
        $this->baseDir = $baseDir;
    }

    public function setIdephix(IdephixInterface $idx)
    {
        $this->idx = $idx;
    }

    /**
     * Based by composer self-update
     */
    public function initFile()
    {
        $idxFile = $this->baseDir . DIRECTORY_SEPARATOR . 'idxfile.php';
        if (file_exists($idxFile)) {
            $this->idx->output->writeln("<error>An idxfile.php already exists, generation skipped.</error>");

            return;
        }

        $this->idx->output->writeln("Creating basic idxfile.php file...");

        $data = <<<'DEFAULT'
<?php

use Idephix\Idephix;
use Idephix\Extension\Deploy\Deploy;
use Idephix\Extension\PHPUnit\PHPUnit;

$localBaseDir = __DIR__;
$sshParams = array(
    'user' => 'myuser',
);

//You could even define callable to
//lazy load values
//$sshParams = array(
//    'user' => function(){
//        return 'myuser'
//    },
//);

/** @var \Idephix\IdephixInterface $idx */
/** @var array $targets will be used to configure Idephix */
/** @var array $sshClient will be used as ssh client. Default is an instance of \Idephix\SSH\SshClient */

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

$idx->
    /**
     * Symfony2 basic deploy
     */
    add(
        'sf2:deploy',
        function($go = false) use ($idx)
        {
            if (!$go) {
                echo "\nDry Run...\n";
            }
            $idx->deploySF2Copy($go);
        }
    )->
    /**
     * Build your Symfony project after you have downloaded it for the first time
     */
    add(
        'build:fromscratch',
        function () use ($idx)
        {
            if (!file_exists(__DIR__."/composer.phar")) {
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
    )->
    /**
     * Symfony2 installing assets and running assetic command
     */
    add(
        'asset:install',
        function () use ($idx)
        {
            $idx->local("app/console assets:install web");
            $idx->local("app/console assetic:dump");
        }
    )->
    /**
     * run phpunit tests
     */
    add(
        'test:run',
        function () use ($idx)
        {
            $idx->phpunit()->runPhpUnit('-c app/');
        }
    );

$idx->addLibrary('deploy', new Deploy());
$idx->addLibrary('phpunit', new PHPUnit());

DEFAULT;
        if (!is_writable($this->baseDir) || false === file_put_contents($idxFile, $data)) {
            throw new \Exception('Cannot write idxfile.php, check your permission configuration.');
        }

        $this->idx->output->writeln("idxfile.php file created.");
    }
}
