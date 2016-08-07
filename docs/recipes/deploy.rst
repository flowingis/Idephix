Deploying with Idephix
======================

Deploying a PHP application can be done in many ways, this recipe shows you
our best strategy for a generic PHP application, and it is composed of several
steps:

* Preparing the local project
* Preparing the remote server
* Syncing the project to the server
* Linking shared files across releases (configuration files, cache, logs, etc)
* Switching the symlink for the new release, finalizing the deploy

The recipe organize your code on the server using a directory hierarchy borrowed from
`Capistrano <http://capistranorb.com/documentation/getting-started/structure/>`_:

.. code-block:: bash

    ├── current -> /var/www/my_app_name/releases/20150120114500/
    ├── releases
    │   ├── 20150080072500
    │   ├── 20150090083000
    │   ├── 20150100093500
    │   ├── 20150110104000
    │   └── 20150120114500
    └── shared
        └── <linked_files and linked_dirs>

So you can keep multiple releases on the server and switch the current release just creating a symlink to the actual
one you want to make current. This allows you to easily rollback from one release to another.

.. literalinclude:: ../Cookbook/Deploy/idxfile.php
    :language: php
    :caption: idxfile.php
    :emphasize-lines: 3,29,44,59,67
    :linenos:


These tasks are based on several configuration options that you can define in your idxrc.php file:

.. literalinclude:: ../Cookbook/Deploy/idxrc.php
    :language: php
    :caption: idxrc.php
    :emphasize-lines: 4-17,24-26
    :linenos:
