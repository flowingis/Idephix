<?php

use Idephix\Idephix;
use Idephix\Extension\Deploy\Deploy;
use Idephix\Extension\PHPUnit\PHPUnit;
use Idephix\SSH\SshClient;

$idx = new Idephix();

$createPhar = function() use ($idx)
{
    $idx->local('rm -rf /tmp/Idephix && mkdir -p /tmp/Idephix');
    $idx->local("cp -R . /tmp/Idephix");
    $idx->local('cd /tmp/Idephix && composer install --no-dev -o');
    $idx->local('bin/box -vvv build -c /tmp/Idephix/box.json ');
};

$build = function() use ($idx)
{
    $idx->local('composer install --prefer-source');
    $idx->local('bin/phpunit -c tests');
};

$buildTravis = function() use ($idx)
{
    $idx->local('composer install --prefer-source');
    $idx->local('bin/phpunit -c tests --coverage-clover=clover.xml');
};

$idx->add('createPhar', $createPhar);
$idx->add('buildTravis', $buildTravis);
$idx->add('build', $build);
$idx->run();
