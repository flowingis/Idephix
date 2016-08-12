===============
Getting Started
===============

Installing
==========

You can choose to install Idephix where you prefer.
Idephix will use (or create for you) the configuration file in the current path.

Installing Idephix
******************

You can download the phar directly from getidephix.com

.. code-block:: bash

    $ curl http://getidephix.com/idephix.phar > /usr/local/bin/idx
    $ chmod a+x /usr/local/bin/idx

Alternatively you can install it through composer

.. code-block:: bash

    $ composer require ideato/idephix --dev

Basic Usage
***********

Idephix is a tool for running tasks. As a developer your main focus
will be on writing tasks inside a file called ``idxfile.php``. You will
also needo to specify some configurations inside a file called ``idxrc.php``.

Fortunately you won't need to create those files manually, Idephix can generate
them for you.

.. code-block:: bash

    $ idx init-idx-file

This will generate an ``idxfile.php`` and a ``idxrc.php`` file that you can
use as a boiler plate for your automated tasks.

Basically Idephix is a tool for running tasks either remote or local. Remote tasks
can be run against a chosen target (usually a specific server or environment) connecting
to it through ssh (see :ref:`idx_config` for more information on ssh connection and targets).
Local tasks are run on the local host without any need to establish an ssh connection.