<?php

$environments = array(
    'prod' => array(
        'hosts' => array('127.0.0.1'),
        'ssh_params' => array(
            'user' => 'ideato'
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

return \Idephix\Config::fromArray(
    array(
        \Idephix\Config::ENVS => $environments,
        \Idephix\Config::SSHCLIENT => new \Idephix\SSH\SshClient(new \Idephix\SSH\CLISshProxy()),
        \Idephix\Config::EXTENSIONS => array(
            'rsync' => new \Idephix\Extension\Project\Rsync(),
        ),
    )
);
