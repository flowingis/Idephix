[![Stories in Ready](https://badge.waffle.io/ideatosrl/Idephix.png?label=backlog&title=Get%20Involved)](https://waffle.io/ideatosrl/Idephix)
[![Gitter](https://badges.gitter.im/ideatosrl/Idephix.svg)](https://gitter.im/ideatosrl/Idephix?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge)
[![Build Status](https://travis-ci.org/ideatosrl/Idephix.svg)](https://travis-ci.org/ideatosrl/Idephix)
[![Read the docs](https://img.shields.io/badge/docs-latest-brightgreen.svg?style=flat)](http://idephix.readthedocs.io/en/latest/)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/47596bd6-4ac9-4314-b79a-1f2e50292c1f/mini.png)](https://insight.sensiolabs.com/projects/47596bd6-4ac9-4314-b79a-1f2e50292c1f)
[![Latest Stable Version](https://poser.pugx.org/ideato/idephix/version)](https://packagist.org/packages/ideato/idephix)
[![Total Downloads](https://poser.pugx.org/ideato/idephix/downloads)](https://packagist.org/packages/ideato/idephix)
[![Monthly Downloads](https://poser.pugx.org/ideato/idephix/d/monthly)](https://packagist.org/packages/ideato/idephix)
[![License](https://poser.pugx.org/ideato/idephix/license)](https://packagist.org/packages/ideato/idephix)

Idephix - Automation and Deploy tool
====================================

Idephix is a PHP automation tool useful to perform remote and local tasks. It can be used to deploy applications, rotate logs, synchronize data repository across server or create a build system. If you want to learn more about how to use it
[read the docs][rd].

Installation / Usage
--------------------

1. Download the [`idephix.phar`](http://getidephix.com/idephix.phar) executable and init your idxfile.

    ``` sh
    $ curl -LSs http://getidephix.com/idephix.phar > idephix.phar
    $ chmod a+x idephix.phar
    $ idx init-idx-file
    ```

2. Now you can define tasks just defining php functions in your `idxfile.php`

    ```php
    <?php
    
    /**
     * Execute the touch of a file specified in input
     * @param string $name the name of the file to be touch-ed
     * @param bool   $go   if not specified the script execute a dry-run
     */
    function testParams(\Idephix\Context $context, $name, $go = false)
    {
         $context->local('touch /tmp/'.$name);
         $context->remote('touch /tmp/'.$name.'_remote');
    }

    ```
    
For more information about how to define tasks, configuration for multiple environments and much more [read the docs][rd].
Global installation of Idephix

Deploying with Idephix
----------------------

Idephix is good for many different jobs, but we like to use it especially for application deployment. In fact 
out of the box your `idxfile.php` will be initialized using our recipe for [PHP application deployment][idx-deploy] 
that you can use as a starting point for your projects.

Requirements
------------

PHP 5.3.2 or above, >=5.3.12 recommended

Authors
-------

* Manuel 'Kea' Baldssarri <mb@ideato.it>
* Michele 'Orso' Orselli <mo@ideato.it>
* Filippo De Santis <fd@ideato.it>
* [other contributors](https://github.com/ideatosrl/idephix/graphs/contributors)

License
-------

Idephix is licensed under the MIT License - see the LICENSE file for details

[rd]: http://idephix.readthedocs.io/en/latest/
[idx-deploy]: http://idephix.readthedocs.io/en/latest/recipes/deploy.html
