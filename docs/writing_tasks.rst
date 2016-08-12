.. _writing_tasks:

.. highlight:: php
   :linenothreshold: 3

==============
Defining Tasks
==============

To define a new task you just need to define a function within the ``idxfile.php`` and
it will be automatically mounted as an Idephix command.

::

    <?php

    function myNewTask()
    {
        echo 'I am a brand new task' . PHP_EOL;
    }

Now running idx you'll get

.. code-block:: bash

    $ bin/idx

    $ Available commands:
    $ help            Displays help for a command
    $ init-idx-file   Create an example idxfile.php
    $ list            Lists commands
    $ myNewTask

And you can execute it with:

.. code-block:: bash

    $ bin/idx myNewTask
    I am a brand new task

You can even execute a task within another task:

::

    <?php

    function anotherTask()
    {
    }

    function myNewTask(\Idephix\Context $context)
    {
        $context->anotherTask();
    }

.. hint::

    Every task can define a special arguments: ``$context``. If you define an argument and type hint it as
    ``\Idephix\Context`` an instance of the context object will be injected at runtime. The context object allows
    you to execute tasks and access configuration parameters. For more info about ``Context`` check
    out `Scripting with Idephix`_ section

Adding task arguments
=====================

Function parameters will be used as the task arguments.

::

    <?php

    function yell($what)
    {
        echo $what . PHP_EOL;
    }

Mandatory Arguments
-------------------
The parameter $name will be a mandatory option to be specified in the command execution.

.. code-block:: bash

    $ bin/idx help yell
    Usage:
        yell what

    Arguments:
        what

You can add as many arguments as you need, just adding function parameters.

Optional Arguments
------------------

If you want to add optional arguments, just define a default value for the
parameter, as:

::

    <?php

    function yell($what = 'foo')
    {
        echo $what . PHP_EOL;
    }

Optional arguments as task flags
--------------------------------

A flag is a special parameter with default value false.
Using flags should be useful to implement a dry-run approach in your script

::

    <?php

    function deploy($go = false){
         if ($go) {
             //bla bla bla
         return;
     }
 }

Documenting tasks
=================

Tasks and arguments can have a description. You can define descriptions using
simple and well known phpdoc block.

::

    <?php

    /**
     * This command will yell at you
     *
     *
     * @param string $what What you want to yell
     */
    function yell($what = 'foo')
    {
        echo $what . PHP_EOL;
    }

Configure a task like

.. code-block:: bash

    $ bin/idx help yell
    Usage:
        yell [what]

    Arguments:
        what    What you want to yell (default: "foo")

Scripting with Idephix
======================

With Idephix you compose your script basically:

* executing local commands
* executing remote commands
* executing other tasks you have already defined
* sending some output to the console

In order to perform such operations you will need an instance of the ``Idephix\\Context`` object. Idephix will inject it
at runtime in each tasks that defines an argument type hinted as ``Idephix\\Context``. A ``Context`` implements
``\Idephix\TaskExecutor`` and ``\Idephix\DictionaryAccess`` allowing you to execute commands and to access the configuration
data related to the choosen ``env``.

Executing local commands
------------------------

``\Idephix\TaskExecutor::local`` allows you to execute local commands. A local command will be executing without any
need for a SSH connection, on your local machine.

.. code-block:: php
    :linenos:
    :emphasize-lines: 3,4

    <?php

    function buildDoc(\Idephix\Context $context, $open = false)
    {
        $context->local('cp -r src/Idephix/Cookbook docs/');
        $context->local('make  -C ./docs html');

        if ($open) {
            $context->openDoc();
        }
    }

If you need so you can execute the command in dry run mode

.. code-block:: php
    :linenos:
    :emphasize-lines: 3

    <?php

    function buildDoc(\Idephix\Context $context, $open = false)
    {
        $context->local('cp -r src/Idephix/Cookbook docs/', true);
    }

In dry run mode the command will just be echoed to the console. This can be useful while debugging your idxfile to check
the actual command that would be executed.

For local commands you can also specify a timeout:

.. code-block:: php
    :linenos:
    :emphasize-lines: 5

    <?php

    function buildTravis(\Idephix\Context $context)
    {
        try {
            $context->local('composer install');
            $context->local('bin/phpunit -c tests --coverage-clover=clover.xml', false, 240);
            $context->runTask('createPhar');
        } catch (\Exception $e) {
            $context->output()->writeln(sprintf("<error>Exception: \n%s</error>", $e->getMessage()));
            exit(1);
        }
    };

Executing remote commands
-------------------------

Running remote commands is almost the same as running local commands. You can do that using
``\Idephix\TaskExecutor::remote`` method. Dry run mode works quite the same as for local commands, but mind that
`at the moment is not possible to specify a timeout for remote commands`.

::

    <?php

    function switchToNextRelease(Idephix\Context $context, $remoteBaseDir, $nextRelease, $go = false)
    {
        $context->remote(
            "
            cd $remoteBaseDir && \\
            ln -nfs $nextRelease current",
            !$go
        );
    }

In order to execute a remote command you must specify a target environment using ``--env`` option. If you fail to
specify a valid env name you will get an error and the command will not be executed.

Executing user defined tasks
----------------------------

Every task that you define will be accessible as a method of the ``Idephix\Context`` object.
Mind that you don't have to manually inject the ``Context`` object, Idephix will do that for you at runtime.

.. code-block:: php
    :linenos:
    :emphasize-lines: 7,11

    <?php

    function buildDoc(\Idephix\Context $context, $open = false)
    {
        $context->local('cp -r src/Idephix/Cookbook docs/');
        $context->local('make  -C ./docs html');

        if ($open) {
            $context->openDoc();
        }
    }

    function openDoc(\Idephix\Context $context)
    {
        $context->local('open docs/_build/html/index.html');
    }

Accessing configuration from tasks
----------------------------------

``Idephix\Context`` object gives you also access to every configuration defined for the current target.
Imagine you have defined this configuration:

::

    <?php

    $targets = array(
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
        'test' => array(//blablabla),
    );

While executing a command using ``--env=prod`` option your tasks will receive a ``Context`` filled up with prod data, so
you can access to it. ``Context`` allows you to access configuration data implementing php ``\ArrayAccess`` interface or
through get ``\Idephix\DictionaryAccess::get`` method.

::

    <?php

    function deploy(Idephix\Context $context, $go = false)
    {
        $sharedFiles = $context->get('deploy.shared_files', array());
        $repository = $context['deploy.repository'];
        //cut


Writing output to the console
-----------------------------

Idephix is based on Symfony console component so you can send output to the user using the
``\Symfony\Component\Console\Output\OutputInterface``. You can get the full ``OutputInterface`` component
through the ``\Idephix\TaskExecutor::output`` method or you can use the shortcut methods:
``\Idephix\TaskExecutor::write`` and ``\Idephix\TaskExecutor::writeln``.

Here is an example of you you can send some output to the console.

::

    <?php
    
    /**
     * This command will yell at you
     *
     * @param string $what What you want to yell
     */
    function yell(\Idephix\Context $context, $what = 'foo')
    {
        $context->writeln(strtoupper($what));
        $context->write(strtoupper($what) . PHP_EOL);
        $context->output()->write(strtoupper($what) . PHP_EOL);
        $context->output()->writeln(strtoupper($what));
    }

.. hint::

    For more information about ``OutputInterface`` read the official
    component `documentation <http://symfony.com/doc/2.8/components/console.html>`_
