<?php
namespace Idephix\Extension\Deploy;

use Idephix\Tests\Test\IdephixTestCase;
use Idephix\Extension\Deploy\Deploy;

class DeployTest extends IdephixTestCase
{
    public function initDeploy($strategy = null, $config = array())
    {
        $defaultConfig = array(
            'hosts' => array('banana.com'),
            'ssh_params' => array('user' => 'kea', 'ssh_port' => 23),
            'deploy' => array(
                'local_base_dir' => 'local_dir',
                'remote_base_dir' => "/tmp/temp_dir",
                'rsync_exclude_file' => 'rsync_exclude.txt',
                'rsync_include_file' => 'rsync_include.txt',
                'shared_folders' => array (
                    'app/logs',
                    'web/uploads'
                ),
                'shared_symlinks' => array (
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

        $this->deploy = new Deploy();
        $this->idx = $this->getIdephixMock($targets, 'banana');
        $this->deploy->setIdephix($this->idx);
    }

    /**
     * Tests pass non-existent class as deploy strategy
     *
     * @expectedException Exception
     * @expectedExceptionMessage No deploy strategy Idephix\Extension\Deploy\Strategy\FailStrategy found. Check you configuration.
     */
    public function testWrongStrategy()
    {
        $this->initDeploy('FailStrategy');
        $this->deploy->deploySF2Copy(true);
    }

    public function testDeploySf2CopyWithoutSpecificEnvConfiguration()
    {
        $this->initDeploy();
        $result = $this->deploy->deploySF2Copy(true);

        $nextReleaseDir = $this->deploy->getNextReleaseFolder();
        $nextReleaseName = $this->deploy->getNextReleaseName();
        $currentReleaseDir = $this->deploy->getCurrentReleaseFolder();

        $user = $this->idx->sshClient->getUser();
        $this->assertEquals('kea', $user);

        $host = $this->idx->sshClient->getHost();
        $this->assertEquals('banana.com', $host);

        $port = $this->idx->sshClient->getPort();
        $this->assertEquals('23', $port);

        $expected = "Remote: ls /tmp/temp_dir/current
Host ready banana.com
Remote: mkdir -p $nextReleaseDir
Copy code to the next release dir
Remote: cp -pPR '$currentReleaseDir/.' '$nextReleaseDir'
Sync code to the next release
Local: rsync -rlpDvcz --delete -e 'ssh -p $port'  --exclude-from=rsync_exclude.txt --include-from=rsync_include.txt local_dir/ $user@$host:$nextReleaseDir
Creating shared folders...
Creating shared folder app/logs ...
Remote: mkdir -p /tmp/temp_dir/shared/app/logs
Creating shared folder web/uploads ...
Remote: mkdir -p /tmp/temp_dir/shared/web/uploads
Updating symlink for shared folder ..
Creating shared symlink for $nextReleaseDir/app/logs ...
Remote: [ -e '/tmp/temp_dir/shared/app/logs' ]
Remote: [ -e '$nextReleaseDir/app/logs' ]
Remote: unlink $nextReleaseDir/app/logs || rmdir $nextReleaseDir/app/logs || rm -R $nextReleaseDir/app/logs
Remote: ln -nfs /tmp/temp_dir/shared/app/logs $nextReleaseDir/app/logs
Creating shared symlink for $nextReleaseDir/web/uploads ...
Remote: [ -e '/tmp/temp_dir/shared/web/uploads' ]
Remote: [ -e '$nextReleaseDir/web/uploads' ]
Remote: unlink $nextReleaseDir/web/uploads || rmdir $nextReleaseDir/web/uploads || rm -R $nextReleaseDir/web/uploads
Remote: ln -nfs /tmp/temp_dir/shared/web/uploads $nextReleaseDir/web/uploads
Remote: cd $nextReleaseDir && rm -Rf app/cache/*
Switch to next release...
Remote: cd /tmp/temp_dir/ && ln -s releases/$nextReleaseName next && mv -fT next current
Asset and assetic stuff...
Remote: cd /tmp/temp_dir/current && php app/console assets:install --symlink web --env=dev
Remote: cd /tmp/temp_dir/current && php app/console assetic:dump --env=dev --no-debug
Remote: cd '/tmp/temp_dir/releases/' && ls | sort | head -n -6 | xargs rm -Rf
";

        rewind($this->output);
        $actualOutput = stream_get_contents($this->output);

        $this->assertEquals($expected, $actualOutput);
    }

    public function testDeploySf2CopyWithEnvConfiguration()
    {
        $this->initDeploy(null, array('symfony_env' => 'prod'));
        $result = $this->deploy->deploySF2Copy(true);

        $nextReleaseDir = $this->deploy->getNextReleaseFolder();
        $nextReleaseName = $this->deploy->getNextReleaseName();
        $currentReleaseDir = $this->deploy->getCurrentReleaseFolder();

        $user = $this->idx->sshClient->getUser();
        $this->assertEquals('kea', $user);

        $host = $this->idx->sshClient->getHost();
        $this->assertEquals('banana.com', $host);

        $port = $this->idx->sshClient->getPort();
        $this->assertEquals('23', $port);

        $expected = "Remote: ls /tmp/temp_dir/current
Host ready banana.com
Remote: mkdir -p $nextReleaseDir
Copy code to the next release dir
Remote: cp -pPR '$currentReleaseDir/.' '$nextReleaseDir'
Sync code to the next release
Local: rsync -rlpDvcz --delete -e 'ssh -p $port'  --exclude-from=rsync_exclude.txt --include-from=rsync_include.txt local_dir/ $user@$host:$nextReleaseDir
Creating shared folders...
Creating shared folder app/logs ...
Remote: mkdir -p /tmp/temp_dir/shared/app/logs
Creating shared folder web/uploads ...
Remote: mkdir -p /tmp/temp_dir/shared/web/uploads
Updating symlink for shared folder ..
Creating shared symlink for $nextReleaseDir/app/logs ...
Remote: [ -e '/tmp/temp_dir/shared/app/logs' ]
Remote: [ -e '$nextReleaseDir/app/logs' ]
Remote: unlink $nextReleaseDir/app/logs || rmdir $nextReleaseDir/app/logs || rm -R $nextReleaseDir/app/logs
Remote: ln -nfs /tmp/temp_dir/shared/app/logs $nextReleaseDir/app/logs
Creating shared symlink for $nextReleaseDir/web/uploads ...
Remote: [ -e '/tmp/temp_dir/shared/web/uploads' ]
Remote: [ -e '$nextReleaseDir/web/uploads' ]
Remote: unlink $nextReleaseDir/web/uploads || rmdir $nextReleaseDir/web/uploads || rm -R $nextReleaseDir/web/uploads
Remote: ln -nfs /tmp/temp_dir/shared/web/uploads $nextReleaseDir/web/uploads
Remote: cd $nextReleaseDir && rm -Rf app/cache/*
Switch to next release...
Remote: cd /tmp/temp_dir/ && ln -s releases/$nextReleaseName next && mv -fT next current
Asset and assetic stuff...
Remote: cd /tmp/temp_dir/current && php app/console assets:install --symlink web --env=prod
Remote: cd /tmp/temp_dir/current && php app/console assetic:dump --env=prod --no-debug
Remote: cd '/tmp/temp_dir/releases/' && ls | sort | head -n -6 | xargs rm -Rf
";

        rewind($this->output);
        $actualOutput = stream_get_contents($this->output);

        $this->assertEquals($expected, $actualOutput);
    }

    public function testDeploySf2WithMigrations()
    {
        $this->initDeploy(
            null,
            array(
                'deploy' => array(
                    'local_base_dir' => 'local_dir',
                    'remote_base_dir' => "/tmp/temp_dir",
                    'rsync_exclude_file' => 'rsync_exclude.txt',
                    'rsync_include_file' => 'rsync_include.txt',
                    'migrations' => true,
                    'shared_folders' => array(
                        'app/logs',
                        'web/uploads'
                    ),
                    'shared_symlinks' => array (
                        'app/logs',
                        'web/uploads'
                    ),
                )
            )
        );
        $result = $this->deploy->deploySF2Copy(true);

        $nextReleaseDir = $this->deploy->getNextReleaseFolder();
        $nextReleaseName = $this->deploy->getNextReleaseName();
        $currentReleaseDir = $this->deploy->getCurrentReleaseFolder();

        $user = $this->idx->sshClient->getUser();
        $this->assertEquals('kea', $user);

        $host = $this->idx->sshClient->getHost();
        $this->assertEquals('banana.com', $host);

        $port = $this->idx->sshClient->getPort();
        $this->assertEquals('23', $port);

        $expected = "Remote: ls /tmp/temp_dir/current
Host ready banana.com
Remote: mkdir -p $nextReleaseDir
Copy code to the next release dir
Remote: cp -pPR '$currentReleaseDir/.' '$nextReleaseDir'
Sync code to the next release
Local: rsync -rlpDvcz --delete -e 'ssh -p $port'  --exclude-from=rsync_exclude.txt --include-from=rsync_include.txt local_dir/ $user@$host:$nextReleaseDir
Creating shared folders...
Creating shared folder app/logs ...
Remote: mkdir -p /tmp/temp_dir/shared/app/logs
Creating shared folder web/uploads ...
Remote: mkdir -p /tmp/temp_dir/shared/web/uploads
Updating symlink for shared folder ..
Creating shared symlink for $nextReleaseDir/app/logs ...
Remote: [ -e '/tmp/temp_dir/shared/app/logs' ]
Remote: [ -e '$nextReleaseDir/app/logs' ]
Remote: unlink $nextReleaseDir/app/logs || rmdir $nextReleaseDir/app/logs || rm -R $nextReleaseDir/app/logs
Remote: ln -nfs /tmp/temp_dir/shared/app/logs $nextReleaseDir/app/logs
Creating shared symlink for $nextReleaseDir/web/uploads ...
Remote: [ -e '/tmp/temp_dir/shared/web/uploads' ]
Remote: [ -e '$nextReleaseDir/web/uploads' ]
Remote: unlink $nextReleaseDir/web/uploads || rmdir $nextReleaseDir/web/uploads || rm -R $nextReleaseDir/web/uploads
Remote: ln -nfs /tmp/temp_dir/shared/web/uploads $nextReleaseDir/web/uploads
Remote: cd $nextReleaseDir && ./app/console doctrine:migration:migrate --env=dev
Remote: cd $nextReleaseDir && rm -Rf app/cache/*
Switch to next release...
Remote: cd /tmp/temp_dir/ && ln -s releases/$nextReleaseName next && mv -fT next current
Asset and assetic stuff...
Remote: cd /tmp/temp_dir/current && php app/console assets:install --symlink web --env=dev
Remote: cd /tmp/temp_dir/current && php app/console assetic:dump --env=dev --no-debug
Remote: cd '/tmp/temp_dir/releases/' && ls | sort | head -n -6 | xargs rm -Rf
";

        rewind($this->output);
        $actualOutput = stream_get_contents($this->output);

        $this->assertEquals($expected, $actualOutput);
    }
    
    /**
     * Tests pass non-existent class as deploy strategy
     *
     * @expectedException Exception
     * @expectedExceptionMessage In "banana" target, deploy.shared_folders parameter was set but deploy.shared_symlinks was not. You must define deploy.shared_symlinks too.
     */
    public function testDeploySf2CopyWithoutSpecificSharedSymlinksToo()
    {
        $this->initDeploy(
            null,
            array(
                'deploy' => array(
                    'local_base_dir' => 'local_dir',
                    'remote_base_dir' => "/tmp/temp_dir",
                    'shared_folders' => array (
                        'app/logs',
                        'web/uploads'
                    )
                )
            )
        );
        $result = $this->deploy->deploySF2Copy(true);
    }
    
    public function testDeploySf2CopySpecifyingEmptySharedSymlinks()
    {
        $this->initDeploy(
            null,
            array(
                'deploy' => array(
                    'local_base_dir' => 'local_dir',
                    'remote_base_dir' => "/tmp/temp_dir",
                    'shared_folders' => array (
                        'app/logs',
                        'web/uploads'
                    ),
                    'shared_symlinks' => array()
                )
            )
        );
        $result = $this->deploy->deploySF2Copy(true);
        //No Exceptions
    }
}
