===============
Getting Started
===============

Installing
**********

You can install Idephix in several ways:

As a phar (Recommended)
-----------------------

You can download the phar directly from getidephix.com

.. code-block:: bash

    $ curl -LSs http://getidephix.com/idephix.phar > idephix.phar
    $ chmod a+x idephix.phar

We recommend you to download the phar and put it under version control with your project, so you can have the best
control over used version and you'll be sure to avoid dependencies conflicts with your project.

As a composer dependency
------------------------

.. code-block:: bash

    $ composer require ideato/idephix --dev

Globally using homebrew
-----------------------

.. code-block:: bash

    $ brew tap ideatosrl/php
    $ brew install idephix


Basic Usage
***********

Idephix is a tool for running tasks. As a developer your main focus
will be on writing tasks (as php functions) inside a file called ``idxfile.php``.
You will also need to specify some configurations inside a file called ``idxrc.php``.

Fortunately you won't need to create those files manually, Idephix can generate
them for you.

.. code-block:: bash

    $ idx init-idx-file

This will generate an ``idxfile.php`` and a ``idxrc.php`` file that you can
use as a boiler plate for your automated tasks.

Basically Idephix is a tool for running tasks either remote or local. Remote tasks
can be run against a chosen environment connecting
to it through ssh (see :ref:`idx_config` for more information on ssh connection and environments).

Local tasks are run on the local host without any need to establish an ssh connection.