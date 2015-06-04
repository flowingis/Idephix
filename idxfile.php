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

    $branch = getenv('TRAVIS_BRANCH');
    $pr = getenv('TRAVIS_PULL_REQUEST');

    if ('master' != $branch || $pr) {
        $idx->output->writeln("skipping (branch '$branch', is PR: '$pr'");
        exit(0);
    }

    $idx->output->writeln('decrypting rsa key...');

    $key = '$encrypted_b26b356be257_key';
    $iv = '$encrypted_b26b356be257_iv';
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

$releasePhar = function() use ($idx) {
    $idx->local('echo Releasing new phar version...');

    // $branch = getenv('TRAVIS_BRANCH');
    // $pr = getenv('TRAVIS_PULL_REQUEST');

    // if ('master' != $branch || $pr) {
    //     echo "skipping phar release branch $branch, PR $pr";
    //     exit(0);
    // }

    // decrypt and add key
    // $key = '$encrypted_b26b356be257_key';
    // $iv = '$encrypted_b26b356be257_iv';

    // $idx->local('mkdir -p ~/.ssh');
    // $idx->local("openssl aes-256-cbc -K $key -iv $iv -in ./id_rsa_idephix_doc.enc -out ~/.ssh/id_rsa_idephix_doc -d");
    // $idx->local('chmod 600 ~/.ssh/id_rsa_idephix_doc');
    // $idx->local('ssh-add ~/.ssh/id_rsa_idephix_doc');

    // clone doc repo
    // $idx->local('cd ~ && git clone --branch gh-pages git@github.com:ideatosrl/getidephix.com.git docs');
    // $idx->local('cd ~/docs && git config user.name "ideatobot"');
    // $idx->local('cd ~/docs && git config user.email "info@ideato.it"');

    // if (!file_exists('./idephix.phar')) {
    //     echo 'Idephix phar does not exists';
    //     exit(-1);
    // }

    //copy new phar & commit
    // $idx->local('cp -f idephix.phar ~/docs');
    // $idx->local('cp ~/docs && git add -A .');
    // $idx->local('cp ~/docs && git commit -qm "release new idephix version"');
    // $idx->local('cp ~/docs && git push -q origin gh-pages');

    // $version = $idx->local('cat /tmp/Idephix/.git/refs/heads/master');
    // $idx->local(sprintf('cd /tmp/getidephix && git add . && git commit -m "Deploy phar version %s" && git push origin', $version));
    // $idx->local('cp /tmp/Idephix/.git/refs/heads/master /tmp/getidephix/version');
};

$idx->add('deployPhar', $deployPhar);
$idx->add('createPhar', $createPhar);
$idx->add('releasePhar', $releasePhar);
$idx->add('buildTravis', $buildTravis);
$idx->add('build', $build);
$idx->run();
