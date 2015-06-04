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

$deployPhar = function() use ($idx)
{
    $idx->output->writeln('Releasing new phar version...');

    $idx->local('mkdir -p ~/.ssh');
    $idx->local('chmod 600 ~/.ssh/id_rsa');

    // clone doc repo
    $idx->local('cd ~ && git clone --branch gh-pages git@github.com:ideatosrl/getidephix.com.git docs');
    $idx->local('cd ~/docs && git config user.name "ideatobot"');
    $idx->local('cd ~/docs && git config user.email "info@ideato.it"');

    if (!file_exists('./idephix.phar')) {
        echo 'Idephix phar does not exists';
        exit(-1);
    }

    // copy new phar & commit
    $idx->local('cp -f idephix.phar ~/docs');
    $out = $idx->local('cd ~/docs && git status');
    $idx->output->writeln($out);

        // $idx->local('cp ~/docs && git push -q origin gh-pages');

    // $version = $idx->local('cat /tmp/Idephix/.git/refs/heads/master');
    // $idx->local(sprintf('cd /tmp/getidephix && git add . && git commit -m "Deploy phar version %s" && git push origin', $version));
    // $idx->local('cp /tmp/Idephix/.git/refs/heads/master /tmp/getidephix/version');
};

$createPhar = function() use ($idx)
{
    echo "Creating phar...\n";
    $idx->local('rm -rf /tmp/Idephix && mkdir -p /tmp/Idephix');
    $idx->local("cp -R . /tmp/Idephix");
    $idx->local("cd /tmp/Idephix && rm -rf vendor");
    $idx->local("cd /tmp/Idephix && git checkout -- .");
    $idx->local('cd /tmp/Idephix && composer install --prefer-source --no-dev -o');
    $idx->local('bin/box build -c /tmp/Idephix/box.json ');

    echo "Smoke testing...\n";
    $out = $idx->local('php idephix.phar');

    if (false === strpos($out, 'Idephix version')) {
        echo "Error!\n";
        exit(-1);
    }

    echo "\nAll good!\n";
};

$idx->add('deployPhar', $deployPhar);
$idx->add('createPhar', $createPhar);
$idx->add('buildTravis', $buildTravis);
$idx->add('build', $build);
$idx->run();
