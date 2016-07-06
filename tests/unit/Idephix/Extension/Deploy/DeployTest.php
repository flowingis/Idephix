<?php
namespace Idephix\Extension\Deploy;

use Idephix\Test\Extension\Deploy\Strategy\StubFactory;
use Idephix\Test\IdephixTestCase;
use Idephix\Test\InspectableIdephix;

class DeployTest extends IdephixTestCase
{
    /** @var  InspectableIdephix */
    private $idx;

    /**
     * @var Deploy
     */
    private $deploy;

    /**
     * Tests pass non-existent class as deploy strategy
     *
     * @expectedException Exception
     * @expectedExceptionMessage No deploy strategy MissingStrategy found. Check you configuration.
     */
    public function testWrongStrategy()
    {
        $this->initDeploy('MissingStrategy');
        $this->deploy->deploySF2Copy(true);
    }

    public function testDeploySf2CopyWithoutSpecificEnvConfiguration()
    {
        $this->initDeploy();
        $this->deploy->deploySF2Copy(true);

        $nextReleaseDir = $this->deploy->getNextReleaseFolder();
        $nextReleaseName = $this->deploy->getNextReleaseName();
        $currentReleaseDir = $this->deploy->getCurrentReleaseFolder();
        $user = $this->idx->sshClient()->getUser();
        $host = $this->idx->sshClient()->getHost();
        $port = $this->idx->sshClient()->getPort();

        $expectedCommands = array(
            'Remote: ls /tmp/temp_dir/current',
            "Remote: mkdir -p $nextReleaseDir",
            'Local: rsync command using timeout of: 60',
            "Remote: [ -e '$nextReleaseDir/app/logs' ]",
            "Remote: unlink $nextReleaseDir/app/logs || rmdir $nextReleaseDir/app/logs || rm $nextReleaseDir/app/logs",
            "Remote: ln -nfs /tmp/temp_dir/shared/app/logs $nextReleaseDir/app/logs",
            "Remote: [ -e '$nextReleaseDir/web/uploads' ]",
            "Remote: unlink $nextReleaseDir/web/uploads || rmdir $nextReleaseDir/web/uploads || rm $nextReleaseDir/web/uploads",
            "Remote: ln -nfs /tmp/temp_dir/shared/web/uploads $nextReleaseDir/web/uploads",
            "Remote: cd $nextReleaseDir && ./app/console cache:clear --env=dev --no-debug",
            "Remote: cd $nextReleaseDir && php app/console assets:install --symlink web --env=dev",
            "Remote: cd $nextReleaseDir && php app/console assetic:dump --env=dev --no-debug",
            "Remote: cd '/tmp/temp_dir/releases/' && ls | sort | grep -v $(basename $(readlink '$currentReleaseDir')) | head -n -5 | xargs rm -Rf",
            "Remote: cd /tmp/temp_dir/ && ln -s releases/$nextReleaseName next && mv -fT next current",
        );

        $this->assertEquals($expectedCommands, $this->idx->getExecutedCommands());
    }

    public function testDeploySf2CopyWithEnvConfiguration()
    {
        $this->initDeploy(null, array('symfony_env' => 'prod'));
        $this->deploy->deploySF2Copy(true);

        $nextReleaseDir = $this->deploy->getNextReleaseFolder();
        $nextReleaseName = $this->deploy->getNextReleaseName();
        $currentReleaseDir = $this->deploy->getCurrentReleaseFolder();
        $user = $this->idx->sshClient()->getUser();
        $host = $this->idx->sshClient()->getHost();
        $port = $this->idx->sshClient()->getPort();

        $expectedCommand = array(
            'Remote: ls /tmp/temp_dir/current',
            "Remote: mkdir -p $nextReleaseDir",
            'Local: rsync command using timeout of: 60',
            "Remote: [ -e '$nextReleaseDir/app/logs' ]",
            "Remote: unlink $nextReleaseDir/app/logs || rmdir $nextReleaseDir/app/logs || rm $nextReleaseDir/app/logs",
            "Remote: ln -nfs /tmp/temp_dir/shared/app/logs $nextReleaseDir/app/logs",
            "Remote: [ -e '$nextReleaseDir/web/uploads' ]",
            "Remote: unlink $nextReleaseDir/web/uploads || rmdir $nextReleaseDir/web/uploads || rm $nextReleaseDir/web/uploads",
            "Remote: ln -nfs /tmp/temp_dir/shared/web/uploads $nextReleaseDir/web/uploads",
            "Remote: cd $nextReleaseDir && ./app/console cache:clear --env=prod --no-debug",
            "Remote: cd $nextReleaseDir && php app/console assets:install --symlink web --env=prod",
            "Remote: cd $nextReleaseDir && php app/console assetic:dump --env=prod --no-debug",
            "Remote: cd '/tmp/temp_dir/releases/' && ls | sort | grep -v $(basename $(readlink '$currentReleaseDir')) | head -n -5 | xargs rm -Rf",
            "Remote: cd /tmp/temp_dir/ && ln -s releases/$nextReleaseName next && mv -fT next current",
        );

        $this->assertEquals($expectedCommand, $this->idx->getExecutedCommands());
    }

    public function testDeploySf2WithMigrations()
    {
        $this->initDeploy(
            null,
            array(
                'deploy' => array(
                    'local_base_dir' => 'local_dir',
                    'remote_base_dir' => '/tmp/temp_dir',
                    'rsync_exclude_file' => 'rsync_exclude.txt',
                    'rsync_include_file' => 'rsync_include.txt',
                    'migrations' => true,
                    'shared_folders' => array(
                        'app/logs',
                        'web/uploads'
                    ),
                )
            )
        );
        $this->deploy->deploySF2Copy(true);

        $nextReleaseDir = $this->deploy->getNextReleaseFolder();
        $nextReleaseName = $this->deploy->getNextReleaseName();
        $currentReleaseDir = $this->deploy->getCurrentReleaseFolder();

        $user = $this->idx->sshClient()->getUser();
        $host = $this->idx->sshClient()->getHost();
        $port = $this->idx->sshClient()->getPort();

        $expectedCommands = array(
            'Remote: ls /tmp/temp_dir/current',
            "Remote: mkdir -p $nextReleaseDir",
            'Local: rsync command using timeout of: 60',
            "Remote: [ -e '$nextReleaseDir/app/logs' ]",
            "Remote: unlink $nextReleaseDir/app/logs || rmdir $nextReleaseDir/app/logs || rm $nextReleaseDir/app/logs",
            "Remote: ln -nfs /tmp/temp_dir/shared/app/logs $nextReleaseDir/app/logs",
            "Remote: [ -e '$nextReleaseDir/web/uploads' ]",
            "Remote: unlink $nextReleaseDir/web/uploads || rmdir $nextReleaseDir/web/uploads || rm $nextReleaseDir/web/uploads",
            "Remote: ln -nfs /tmp/temp_dir/shared/web/uploads $nextReleaseDir/web/uploads",
            "Remote: cd $nextReleaseDir && ./app/console cache:clear --env=dev --no-debug",
            "Remote: cd $nextReleaseDir && ./app/console doctrine:migration:migrate --env=dev",
            "Remote: cd $nextReleaseDir && php app/console assets:install --symlink web --env=dev",
            "Remote: cd $nextReleaseDir && php app/console assetic:dump --env=dev --no-debug",
            "Remote: cd '/tmp/temp_dir/releases/' && ls | sort | grep -v $(basename $(readlink '$currentReleaseDir')) | head -n -5 | xargs rm -Rf",
            "Remote: cd /tmp/temp_dir/ && ln -s releases/$nextReleaseName next && mv -fT next current",
        );

        $this->assertEquals($expectedCommands, $this->idx->getExecutedCommands());
    }

    public function testDeployWithNonDefaultReleaseFormatName()
    {
        $this->initDeploy(
            null,
            array(
                'deploy' => array(
                    'release_folder_name_format' => 'Y_m_d'
                )
            )
        );

        $releaseFolder = $this->deploy
            ->getNextReleaseFolder();

        $this->assertEquals(date('Y_m_d'), $releaseFolder);
    }

    public function testGetNextReleaseFolderShouldBeIdempotent()
    {
        $this->initDeploy(
            null,
            array(
                'deploy' => array(
                    'release_folder_name_format' => 'Y_m_d-H:i:s'
                )
            )
        );

        $releaseFolder = $this->deploy
            ->getNextReleaseFolder();

        sleep('1');

        $sameReleaseFolder = $this->deploy
            ->getNextReleaseFolder();

        $this->assertEquals($releaseFolder, $sameReleaseFolder);
    }

    public function testDeployWithDefaultReleaseFormatNameWhenFormatIsNull()
    {
        $this->initDeploy();

        $releaseFolder = $this->deploy
            ->getNextReleaseFolder();

        $this->assertEquals(date('YmdHis'), $releaseFolder);
    }

    public function testDeploySF2WithMessageToConcatToReleaseFolder()
    {
        $remoteBaseDir = '/tmp/temp_dir';

        $this->initDeploy(
            null,
            array(
                'deploy' => array(
                    'local_base_dir' => 'local_dir',
                    'remote_base_dir' => $remoteBaseDir,
                    'rsync_exclude_file' => 'rsync_exclude.txt',
                    'rsync_include_file' => 'rsync_include.txt',
                    'release_folder_name_format' => 'Y_m_d',
                    'shared_folders' => array(
                        'app/logs',
                        'web/uploads'
                    ),
                )
            )
        );

        $this->deploy
            ->setMessageForReleaseFolder('banana apple');
        $this->deploy
            ->deploySF2Copy(true, 6, true);

        $releaseFolder = $this->deploy
            ->getNextReleaseFolder();

        $this->assertEquals($remoteBaseDir . '/releases/' .date('Y_m_d') . '-banana-apple', $releaseFolder);
    }

    public function testSetMessageToConcatToReleaseFolder()
    {
        $remoteBaseDir = '/tmp/temp_dir';

        $this->initDeploy(
            null,
            array(
                'deploy' => array(
                    'local_base_dir' => 'local_dir',
                    'remote_base_dir' => $remoteBaseDir,
                    'rsync_exclude_file' => 'rsync_exclude.txt',
                    'rsync_include_file' => 'rsync_include.txt',
                    'release_folder_name_format' => 'Y_m_d',
                    'shared_folders' => array(
                        'app/logs',
                        'web/uploads'
                    ),
                )
            )
        );

        $this->deploy
            ->setMessageForReleaseFolder('banana');

        $releaseFolder = $this->deploy
            ->getNextReleaseFolder();

        $this->assertEquals(date('Y_m_d') . '-banana', $releaseFolder);
    }

    private function initDeploy($strategy = null, $config = array())
    {
        $defaultConfig = array(
            'hosts' => array('banana.com'),
            'ssh_params' => array('user' => 'kea', 'ssh_port' => 23),
            'deploy' => array(
                'local_base_dir' => 'local_dir',
                'remote_base_dir' => '/tmp/temp_dir',
                'rsync_exclude_file' => 'rsync_exclude.txt',
                'rsync_include_file' => 'rsync_include.txt',
                'shared_folders' => array(
                    'app/logs',
                    'web/uploads'
                ),
            )
        );

        $targets = array('banana' =>
            array_merge($defaultConfig, $config),
        );

        if (!is_null($strategy)) {
            $targets['banana']['deploy']['strategy'] = $strategy;
        }

        $this->deploy = new Deploy(new StubFactory());
        $this->idx = $this->getIdephixMock($targets, 'banana');
        $this->deploy->setIdephix($this->idx);
    }
}
