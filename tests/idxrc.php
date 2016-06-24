<?php

$targets = array(
    'prod' => array(
        'hosts' => array('127.0.0.1', '33.33.33.10'),
        'ssh_params' => array(
            'user' => 'ideato'
        ),
        'deploy' => array(
            'local_base_dir' => __DIR__,
            'remote_base_dir' => "/var/www/my-project/",
            'rsync_exclude_file' => 'rsync_exclude.txt',
            'shared_folders' => array(
                'app/logs',
                'web/uploads'
            ),
        )
    ),
    'stage' => array(
        'hosts' => array('192.168.169.170'),
        'ssh_params' => array(
            'user' => 'ideato'
        ),
        'deploy' => array(
            'local_base_dir' => __DIR__,
            'remote_base_dir' => "/var/www/my-project.ideato.it/",
            'rsync_exclude_file' => 'rsync_exclude.txt',
            'shared_folders' => array(
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
            'shared_folders' => array(
                'app/logs',
                'web/uploads'
            ),
        )
    ),
);
