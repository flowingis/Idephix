<?php
namespace Idephix\Extension\Deploy;

use Idephix\Tests\Test\IdephixTestCase;
use Idephix\Extension\Deploy\Deploy;
use Idephix\Config\Config;

class DeployTest extends IdephixTestCase
{
    public function setUp()
    {
        $targets = array('banana' =>
            array(
                'hosts' => array('banana.com'),
                'ssh_params' => array('user' => 'kea', 'ssh_port' => 23),
                'deploy' => array(
                    'local_base_dir' => 'local_dir',
                    'remote_base_dir' => "/tmp/temp_dir",
                    'rsync_exclude_file' => 'rsync_exclude.txt',
                    'rsync_include_file' => 'rsync_include.txt'
                )
            )
        );

        $this->deploy = new Deploy();
        $this->idx = $this->getIdephixMock($targets, 'banana');
        $this->deploy->setIdephix($this->idx);
    }

    public function testDeploySf2Copy()
    {
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
Bootstrap: OK
Remote: mkdir -p $nextReleaseDir
Copy code to the next release dir
Remote: cp -pPR '$currentReleaseDir/.' '$nextReleaseDir'
Sync code to the next release
Local: rsync -rlpDvcz --delete -e 'ssh -p $port'  --exclude-from=rsync_exclude.txt --include-from=rsync_include.txt  $user@$host:$nextReleaseDir
Remote: cd $nextReleaseDir && ./app/console cache:clear --env=prod --no-debug && ./app/console cache:warmup
Switch to next release...
Remote: cd /tmp/temp_dir/ && ln -s releases/$nextReleaseName next && mv -fT next current
Asset and assetic stuff...
Remote: cd /tmp/temp_dir/current && php app/console assets:install --symlink web
Remote: cd /tmp/temp_dir/current && php app/console assetic:dump --env=prod
Remote: cd '/tmp/temp_dir/releases/' && ls | sort | head -n -6 | xargs rm -Rf
";

        rewind($this->output);
        $actualOutput = stream_get_contents($this->output);

        $this->assertEquals($expected, $actualOutput);
    }
}
