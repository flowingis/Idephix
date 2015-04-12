<?php

use Idephix\Idephix;
use Idephix\Extension\Deploy\Deploy;
use Idephix\Extension\PHPUnit\PHPUnit;
use Idephix\SSH\SshClient;

$sshParams = array(
    'user' => 'ideato'
);

$targets = array(
    'prod' => array(
        'hosts' => array('127.0.0.1', '33.33.33.10'),
        'ssh_params' => $sshParams,
        'deploy' => array(
            'local_base_dir' => __DIR__,
            'remote_base_dir' => "/var/www/my-project/",
            'rsync_exclude_file' => 'rsync_exclude.txt',
            'shared_folders' => array (
                'app/logs',
                'web/uploads'
            ),
        )
    ),
    'stage' => array(
        'hosts' => array('192.168.169.170'),
        'ssh_params' => $sshParams,
        'deploy' => array(
            'local_base_dir' => __DIR__,
            'remote_base_dir' => "/var/www/my-project.ideato.it/",
            'rsync_exclude_file' => 'rsync_exclude.txt',
            'shared_folders' => array (
                'app/logs',
                'web/uploads'
            ),
        )
    ),
    'test' => array(
        'hosts' => array('127.0.0.1'),
        'ssh_params' => array('user' => 'kea'),
        'deploy' => array(
            'local_base_dir' => __DIR__,
            'remote_base_dir' => "/tmp/my-project.test/",
            'shared_folders' => array (
                'app/logs',
                'web/uploads'
            ),
        )
    ),
);

$idx = new Idephix($targets, new SshClient());

$idx->
/**
 * Execute a deploy for the specified environment
 * @param bool $go if not given it does a dry-run execution
 */
add('hello',
    function ()
    {
        echo 'Output by custom idx file!';
    })
->add('idephix:test-params',
    function ($param1, $param2, $param3 = 'default')
    {
        echo "$param1 $param2 $param3";
    });

$idx->addLibrary('deploy', new Deploy());
$idx->addLibrary('phpunit', new PHPUnit());
$idx->run();

