<?php


function deployPhar($idx)
{
    $idx->output()->writeln('Releasing new phar version...');

    if (!file_exists('./idephix.phar')) {
        $idx->output()->writeln('Idephix phar does not exists');
        exit(-1);
    }

    $new_version = $idx->local('cat .git/refs/heads/master');
    $commit_msg = trim($idx->local('git log -1 --pretty=%B'));

    if (false === strpos($commit_msg, '[release]')) {
        $idx->output()->writeln("skipping, commit msg was '$commit_msg'");
        exit(0);
    }

    $current_version = file_get_contents(
        'https://raw.githubusercontent.com/ideatosrl/getidephix.com/gh-pages/version'
    );

    if ($new_version == $current_version) {
        $idx->output()->writeln("version $new_version already deployed");
        exit(0);
    }

    $idx->local('mkdir -p ~/.ssh');
    $idx->local('chmod 600 ~/.ssh/id_rsa');

    // clone doc repo
    $idx->local('rm -rf ~/docs');
    $idx->local('cd ~ && git clone --branch gh-pages git@github.com:ideatosrl/getidephix.com.git docs');
    $idx->local('cd ~/docs && git config user.name "ideatobot"');
    $idx->local('cd ~/docs && git config user.email "info@ideato.it"');

    // copy new phar & commit
    $idx->local('cp -f idephix.phar ~/docs');
    $idx->local("cp -f .git/refs/heads/master ~/docs/version");
    $idx->local('cd ~/docs && git status');

    $idx->local('cd ~/docs && git add -A .');
    $idx->local("cd ~/docs && git commit -m 'deploy phar version $new_version'");
    $idx->local('cd ~/docs && git push -q origin gh-pages');
}

function createPhar($idx)
{
    $idx->output()->writeln('Creating phar...');

    $idx->local('rm -rf /tmp/Idephix && mkdir -p /tmp/Idephix');
    $idx->local("cp -R . /tmp/Idephix");
    $idx->local("cd /tmp/Idephix && rm -rf vendor");
    $idx->local("cd /tmp/Idephix && git checkout -- .");
    $idx->local('cd /tmp/Idephix && composer install --prefer-source --no-dev -o');
    $idx->local('bin/box build -c /tmp/Idephix/box.json ');

    $idx->output()->writeln('Smoke testing...');

    $out = $idx->local('php idephix.phar');

    if (false === strpos($out, 'Idephix version')) {
        echo "Error!\n";
        exit(-1);
    }

    $idx->output()->writeln('All good!');
}

function buildTravis($idx)
{
    try {
        $idx->local('composer install --prefer-source');
        $idx->local('bin/phpunit -c tests --coverage-clover=clover.xml');
        $idx->runTask('createPhar');
    } catch(\Exception $e) {
        $idx->output->writeln(sprintf("<error>Exception: \n%s</error>", $e->getMessage()));
        exit(1);
    }
}

function build($idx)
{
    $idx->local('composer install --prefer-source');
    $idx->local('bin/phpunit -c tests');
}
