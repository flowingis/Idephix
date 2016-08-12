.. _writing_tasks:

==============
Defining Tasks
==============

To define a new task you just need to define a function within the `idxfile.php` and
it will be automatically mounted as an Idephix command.

.. code-block:: php
    :linenos:

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

.. code-block:: php
    :linenos:

    function anotherTask()
    {
    }

    function myNewTask(\Idephix\Context $idx)
    {
        $idx->anotherTask();
    }

Adding task arguments
---------------------

Function parameters will be used as the task arguments.

.. code-block:: php
    :linenos:

    function yell($what)
    {
        echo $what . PHP_EOL;
    }

The parameter $name will be a mandatory option to be specified in the command execution.

.. code-block:: bash

    $ bin/idx help yell
    Usage:
        yell what

    Arguments:
        what

You can add as many arguments as you need, just adding function parameters.

If you want to add optional arguments, just define a default value for the
parameter, as:

.. code-block:: php
    :linenos:

    function yell($what = 'foo')
    {
        echo $what . PHP_EOL;
    }

Adding task flags
-----------------

A flag is a special parameter with default value false.
Using flags should be useful to implement a dry-run approach in your script

.. code-block:: php
    :linenos:

    function deploy($go = false){
         if ($go) {
             //bla bla bla
         return;
     }
 }

Documenting tasks
-----------------

Tasks and arguments can have a description. You can define descriptions using
simple and well known phpdoc block.

.. code-block:: php
    :linenos:

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