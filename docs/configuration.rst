.. _idx_config:

Configuration
*************

All Idephix configurations are defined within the ``idxrc.php`` file.
By default Idephix will look for a file named ``idxrc.php`` in the root
directory of your project, but you can store it wherever you want and
name it whatever you want. If you want to use a custom configuration file
you need to specify id by using ``-c`` option with Idephix CLI.

The file **must** return an instace of ``\Idephix\Config``, which lets you
configure the targets and the preferred ssh client.

This example of ``idxrc.php`` file will give you and idea of how define targets
and ssh clients:

.. code-block:: php

    <?php

    $targets = array(
        'prod' => array(
            'hosts' => array('127.0.0.1', '33.33.33.10'),
            'ssh_params' => array(
                'user' => 'ideato'
            ),
            'deploy' => array(
                'local_base_dir' => __DIR__,
                'remote_base_dir' => '/var/www/my-project/',
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
                'remote_base_dir' => '/var/www/my-project.ideato.it/',
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
                'remote_base_dir' => '/tmp/my-project.test/',
                'shared_folders' => array(
                    'app/logs',
                    'web/uploads'
                ),
            )
        ),
    );

    return \Idephix\Config::fromArray(
        array(
            \Idephix\Config::TARGETS => $targets,
            \Idephix\Config::SSHCLIENT => new \Idephix\SSH\SshClient(),
        )
    );

Idephix use ssh-agent to authenticate to remote computers without password.
Otherwise you can specify the password in your script or use ``CLISshProxy``
(instead of the default ``PeclSsh2Proxy``) that ask you the password.

Once you have defined several targets you can specify which one you want to run
your remote task against, using ``--env`` CLI option.