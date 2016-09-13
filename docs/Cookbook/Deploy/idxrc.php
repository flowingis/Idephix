<?php

$environments = array(
    'prod' => array(
        'hosts' => array('127.0.0.1'),
        'ssh_params' => array(
            'user' => 'ideato',
//            'password'             => '',
//            'public_key_file'      => '',
//            'private_key_file'     => '',
//            'private_key_file_pwd' => '',
//            'ssh_port'             => '22'
        ),
        'deploy' => array(
            'repository' => './',
            'branch' => 'origin/master',
            'shared_files' => array('app/config/parameters.yml'),
            'shared_folders' => array('app/cache', 'app/logs'),
            'remote_base_dir' => '/var/www/testidx',
            'rsync_exclude' => './rsync_exclude.txt',
        )
    ),
);

return
    array(
        'envs' => $environments,
        'ssh_client' => new \Idephix\SSH\SshClient(),
        'extensions' => array(
            'rsync' => new \Idephix\Extension\Project\Rsync(),
        ),
    );
