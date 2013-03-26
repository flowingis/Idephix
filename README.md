Idephix - Automation and Deploy tool
====================================

Idephix is a PHP tool for building automatomated scripts

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

You can choose to install idephix wherever you prefer. Idephix use the configuration file in the current path.

1. Go to a PATH directory, e.g. `cd /usr/local/bin`
2. Get Idephix:`curl https://github.com/ideatosrl/Idephix/blob/master/bin/idephix.phar?raw=true > idephix.phar`
3. Make the phar executable `chmod a+x idephix.phar`
4. Go to a project directory, e.g. `cd /path/to/my/project`
5. Define your tasks in the idxfile.php file
5. Just invoke the binary `idephix.phar`
6. You can optionally rename the idephix.phar to idx to make it easy to use

Requirements
------------

PHP 5.3.2 or above, >=5.3.12 recommended

Authors
-------

Manuel 'Kea' Baldssarri <mb@ideato.it>
Michele 'Orso' Orselli <mo@ideato.it>
Filippo De Santis <fd@ideato.it>

License
-------

Idephix is licensed under the MIT License - see the LICENSE file for details
