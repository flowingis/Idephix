.. Idephix documentation master file, created by
   sphinx-quickstart on Thu Jun 30 23:03:52 2016.
   You can adapt this file completely to your liking, but it should at least
   contain the root `toctree` directive.

.. toctree::
   :hidden:
   :maxdepth: 2

   Getting Started <getting_started>
   Configuration <configuration>
   Writing tasks <writing_tasks>
   Extending Idephix <writing_extensions>
   Cookbook <cookbook>
   Migrating your idxfile <migrating_idx_file>

Welcome to Idephix's documentation!
===================================

Idephix is a PHP automation tool useful to perform **remote** and **local** tasks.
It can be used to deploy applications, rotate logs, synchronize data repository
across server or create a build system. The choice is up to you. Idephix is
still in alpha, so things will change. You can report issues and submit PRs
(greatly appreciated :-)) on the `github repo <https://github.com/ideatosrl/Idephix>`_

Basically what you're going to do is define a bunch of function in a php file and execute them from the command line.

.. code-block:: php
    :linenos:

   /**
   * This command will yell at you
   *
   * @param string $what What you want to yell
   */
   function yell(\Idephix\Context $context, $what = 'foo')
   {
      $context->writeln(strtoupper($what));
   }

.. code-block:: bash

   $ bin/idx yell "say my name"
   SAY MY NAME

Requirements
============

PHP 5.3.2 or above, at least 5.3.12 recommended

Authors
=======

`Manuel 'Kea' Baldassarri <http://www.ideato.it/author/kea/>`_,
`Michele 'Orso' Orselli <http://www.ideato.it/author/orso/>`_, `Filippo De Santis <https://github.com/p16>`_
and `other contributors <https://github.com/ideatosrl/idephix/graphs/contributors>`_

License
=======

Idephix is mantained by `ideato <http://www.ideato.it>`_, licensed under the MIT License.