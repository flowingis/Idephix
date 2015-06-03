<?php

use Idephix\Idephix;
use Idephix\Extension\Deploy\Deploy;
use Idephix\Extension\PHPUnit\PHPUnit;
use Idephix\SSH\SshClient;

$idx = new Idephix();

$build = function() use ($idx)
{
    $idx->local('composer install --prefer-source');
    $idx->local('bin/phpunit -c tests');
};

$buildTravis = function() use ($idx)
{
    $idx->local('composer install --prefer-source');
    $idx->local('bin/phpunit -c tests --coverage-clover=clover.xml');
    $idx->runTask('createPhar');
};

$createPhar = function() use ($idx)
{
    echo "Creating phar...\n";
    $idx->local('rm -rf /tmp/Idephix && mkdir -p /tmp/Idephix');
    $idx->local("cp -R . /tmp/Idephix");
    $idx->local("cd /tmp/Idephix && rm -rf vendor");
    $idx->local("cd /tmp/Idephix && git checkout -- .");
    $idx->local('cd /tmp/Idephix && composer install --no-dev -o');
    $idx->local('bin/box build -c /tmp/Idephix/box.json ');

    echo "Smoke testing...\n";
    $out = $idx->local('php idephix.phar');

    if (false === strpos($out, 'Idephix version')) {
        echo "Error!\n";
        exit(0);
    }

    echo "\nAll good!\n";
};

$deployPhar = function () use ($idx) {
    echo "Cloning website repository...\n";
    $idx->local('rm -rf /tmp/getidephix && git clone git@github.com:ideatosrl/getidephix.com.git /tmp/getidephix/');
    $idx->local('cp /tmp/Idephix/idephix.phar /tmp/getidephix/idephix.phar');

    $version = $idx->local('cat /tmp/Idephix/.git/refs/heads/master');
    $idx->local('cp /tmp/Idephix/.git/refs/heads/master /tmp/getidephix/version');
    $idx->local(sprintf('cd /tmp/getidephix && git add . && git commit -m "Deploy phar version %s" && git push origin', $version));

    echo "\nAll good!\n";
};

$idx->add('createPhar', $createPhar);
$idx->add('buildTravis', $buildTravis);
$idx->add('deployPhar', $deployPhar);
$idx->add('build', $build);
$idx->run();
