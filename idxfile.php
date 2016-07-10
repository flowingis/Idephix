<?php
/**
 * @param \Idephix\IdephixInterface $idx
 */
function deployPhar(\Idephix\IdephixInterface $idx)
{
    $idx->output()->writeln('Releasing new phar version...');

    if (!file_exists('./idephix.phar')) {
        $idx->output()->writeln('Idephix phar does not exists');
        exit(-1);
    }

    try{
        $idx->local('git describe --exact-match --tags');
    }catch (\Exception $e){
        $commit_msg = trim($idx->local('git log -1 --pretty=%B'));
        $idx->output()->writeln("skipping, commit '$commit_msg' is not tagged");
        exit(0);
    }

    $current_version = file_get_contents(
        'https://raw.githubusercontent.com/ideatosrl/getidephix.com/gh-pages/version'
    );
    $new_version = $idx->local('git rev-parse HEAD');

    if ($new_version == $current_version) {
        $idx->output()->writeln("version $new_version already deployed");
        exit(0);
    }

    $idx->output()->writeln("setting up ssh key");
    $idx->local('mkdir -p ~/.ssh');
    $idx->local('chmod 600 ~/.ssh/id_rsa');

    $idx->output()->writeln("cloning getidephix website");
    $idx->local('rm -rf ~/docs');
    $idx->local('cd ~ && git clone --branch gh-pages git@github.com:ideatosrl/getidephix.com.git docs');
    $idx->local('cd ~/docs && git config user.name "ideatobot"');
    $idx->local('cd ~/docs && git config user.email "info@ideato.it"');

    $idx->output()->writeln("committing new phar");
    $idx->local('cp -f idephix.phar ~/docs');
    $idx->local('git rev-parse HEAD > ~/docs/version');
    $idx->local('cd ~/docs && git status');

    $idx->local('cd ~/docs && git add -A .');
    $idx->local("cd ~/docs && git commit -m 'deploy phar version $new_version'");
    $idx->local('cd ~/docs && git push -q origin gh-pages');
};

function createPhar(\Idephix\IdephixInterface $idx)
{
    $idx->output()->writeln('Creating phar...');

    $idx->local('rm -rf /tmp/Idephix && mkdir -p /tmp/Idephix');
    $idx->local('cp -R . /tmp/Idephix');
    $idx->local('cd /tmp/Idephix && rm -rf vendor');
    $idx->local('cd /tmp/Idephix && git checkout -- .');
    $idx->local('cd /tmp/Idephix && composer install --prefer-source --no-dev -o');
    $idx->local('bin/box build -c /tmp/Idephix/box.json ');

    $idx->output()->writeln('Smoke testing...');

    $out = $idx->local('php idephix.phar');

    if (false === strpos($out, 'Idephix version')) {
        echo "Error!\n";
        exit(-1);
    }

    $idx->output()->writeln('All good!');
};

function buildTravis(\Idephix\IdephixInterface $idx)
{
    try {
        $idx->local('composer install');
        $idx->local('bin/phpunit -c tests --coverage-clover=clover.xml');
        $idx->runTask('createPhar');
    } catch (\Exception $e) {
        $idx->output()->writeln(sprintf("<error>Exception: \n%s</error>", $e->getMessage()));
        exit(1);
    }
};

function build(\Idephix\IdephixInterface $idx)
{
    $idx->local('composer install --prefer-source');
    $idx->local('bin/phpunit -c tests');
};

function fixCs(\Idephix\IdephixInterface $idx)
{
    $idx->local('bin/php-cs-fixer fix');
};

function buildDoc(\Idephix\IdephixInterface $idx, $open = false)
{
    $idx->local('make  -C ./docs html');

    if ($open) {
        $idx->runTask('openDoc');
    }
}

function openDoc(\Idephix\IdephixInterface $idx)
{
    $idx->local('open docs/_build/html/index.html');
}

/**
 * This command will yell at you
 *
 *
 * @param string $what What you want to yell
 */
function yell($what = 'foo')
{
    echo $what . PHP_EOL;
}