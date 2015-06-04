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

    $version = $idx->local('cat .git/refs/heads/master');

    // copy new phar & commit
    $idx->local('cp -f idephix.phar ~/docs');
    file_put_contents('~/docs/version', $version);
    $idx->local('cd ~/docs && git status');

    $idx->local('cd ~/docs && git add -A .');
    $idx->local("cd ~/docs && git commit -m 'deploy phar version $version'");
    $idx->local('cd ~/docs && git push -q origin gh-pages');
};

$createPhar = function() use ($idx)
{
    $idx->output->writeln('Creating phar...');

    $idx->local('rm -rf /tmp/Idephix && mkdir -p /tmp/Idephix');
    $idx->local("cp -R . /tmp/Idephix");
    $idx->local("cd /tmp/Idephix && rm -rf vendor");
    $idx->local("cd /tmp/Idephix && git checkout -- .");
    $idx->local('cd /tmp/Idephix && composer install --prefer-source --no-dev -o');
    $idx->local('bin/box build -c /tmp/Idephix/box.json ');

    $idx->output->writeln('Smoke testing...');

    $out = $idx->local('php idephix.phar');

    if (false === strpos($out, 'Idephix version')) {
        echo "Error!\n";
        exit(-1);
    }

    $idx->output->writeln('All good!');
};

$idx->add('deployPhar', $deployPhar);
$idx->add('createPhar', $createPhar);
$idx->add('buildTravis', $buildTravis);
$idx->add('build', $build);
$idx->run();
