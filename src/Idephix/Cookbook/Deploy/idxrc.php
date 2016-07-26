<?php

$targets = array(
    'prod' => array(
        'hosts' => array('127.0.0.1'),
        'ssh_params' => array(
            'user' => 'ideato'
        ),
        'deploy' => array(
            'repository' => 'file:///Users/ftassi/workspace/testidx',
            'shared_files' => array('parameters.yml'),
            'shared_folders' => array('cache', 'log'),
            'remote_base_dir' => '/var/www/testidx',
            'rsync_exclude' => './rsync_exclude.txt',
            'rsync_include' => './rsync_include.txt',
        )
    ),
);

return \Idephix\Config::fromArray(
    array(
        \Idephix\Config::TARGETS => $targets,
        \Idephix\Config::SSHCLIENT => array(new \Idephix\SSH\SshClient(new \Idephix\SSH\CLISshProxy())),
    )
);
