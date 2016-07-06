<?php

namespace Idephix\Extension\Deploy\Strategy;

use Idephix\Context;
use Idephix\Test\IdephixTestCase;

class RsyncTest extends IdephixTestCase
{
    /** @test */
    public function it_should_rsync()
    {
        $targets = array(
            'production' => array(
                'hosts' => array('banana.com'),
                'ssh_params' => array('user' => 'kea', 'ssh_port' => 23),
                'deploy' => array(
                    'local_base_dir' => 'local_dir',
                    'remote_base_dir' => '/tmp/temp_dir',
                    'rsync_exclude_file' => './rsync_exclude.txt',
                    'rsync_include_file' => './rsync_include.txt',
                    'shared_folders' => array(
                        'app/logs',
                        'web/uploads'
                    ),
                )
            )
        );

        $idx = $this->getIdephixMock($targets, 'production');
        $currentContext = $this->configureStrategy($idx->getCurrentTarget(), '/releases', '/current', '/next', false);
        $rsync = new Rsync($idx, $currentContext);

        $rsync->deploy();

        $expectedCommands = array(
            "Remote: cp -pPR '/current/.' '/next'",
            "Local: rsync -rlpDvcz --delete -e 'ssh -p 23'  --exclude-from=./rsync_exclude.txt --include-from=./rsync_include.txt local_dir/ kea@banana.com:/next using timeout of: 60"
        );
        
        $this->assertEquals($expectedCommands, $idx->getExecutedCommands());
    }

    /**
     * @param $target
     * @return mixed
     */
    private function configureStrategy(Context $target, $releases, $current, $next, $dryRun)
    {
        $target->set('deploy.releases_dir', $releases);
        $target->set('deploy.current_release_dir', $current);
        $target->set('deploy.next_release_dir', $next);
        $target->set('deploy.dry_run', $dryRun);

        return $target;
    }
}
