Idephix - Automation and Deploy tool
====================================

Idephix is a PHP tool useful to create automation scripts

[![Build Status](https://travis-ci.org/ideatosrl/Idephix.png)](https://travis-ci.org/ideatosrl/Idephix)


Installation / Usage
--------------------

1. Download the [`idephix.phar`](https://github.com/ideatosrl/Idephix/blob/master/bin/idephix.phar?raw=true) executable.

    ``` sh
    $ curl https://github.com/ideatosrl/Idephix/blob/master/bin/idephix.phar?raw=true >idephix.phar
    ```


2. Create a idxfile.php in the root directory of you project. Define your tasks.

    ``` php
    <?php
    
    use Idephix\Idephix;
    use Idephix\SSH\SshClient;
    
    $targets = array(
        'test' => array(
            'hosts' => array('127.0.0.1'),
            'local_base_folder' => __DIR__,
            'remote_base_folder' => "/tmp/my-project.idephix/",
            'ssh_params' => array('user' => 'kea')
        ),
    );
    
    $idx = new Idephix(new SshClient(), $targets);
    
    $idx->
        /**
         * Execute the touch of a file specified in input
         * @param string $name the name of the file to be touch-ed
         * @param bool   $go   if not specified the script execute a dry-run
         */
        add('idephix:test-params',
           function ($name, $go = false) use ($idx) {
             $idx->local('touch /tmp/'.$name);
             $idx->remote('touch /tmp/'.$name.'_remote');
           });
    
    $idx->run();

    ```

3. Run Idephix: `php idephix.phar --env=test idephix:test-params Nome_file`

Global installation of Idephix
----------------------------------------

You can chose to install idephix where you prefer. Idephix will use the configuration file in the corrent path.

1. Change into a directory in your path like `cd /usr/local/bin`
2. Get Idephix `curl https://github.com/ideatosrl/Idephix/blob/master/bin/idephix.phar?raw=true >idephix.phar`
3. Make the phar executable `chmod a+x idephix.phar`
4. Change into a project directory `cd /path/to/my/project`
5. Define your tasks in idxfile.php
5. Use idephix as you normally would `idephix.phar`
6. Optionally you can rename the idephix.phar to idx to make it easier

Requirements
------------

PHP 5.3.2 or above, at least 5.3.12 recommended

Authors
-------

Manuel 'Kea' Baldssarri <mb@ideato.it>
Michele 'Orso' Orselli <mo@ideato.it>
Filippo De Santis <fd@ideato.it>

License
-------

Idephix is licensed under the MIT License - see the LICENSE file for details
