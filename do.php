<?php

require_once __DIR__.'/vendor/autoload.php';

use Ideato\Idephix;
use Ideato\Deploy\Deploy;
use Ideato\PHPUnit\PHPUnit;
use Ideato\SSH\SshClient;
use Ideato\SSH\CLISshProxy;

$idx = new Idephix();

$configFile = getcwd().'/idxfile.php';

if (!is_file($configFile)) {
    echo $configFile." does not exists!\n";

    exit(1);
}

include $configFile;

$sshClient = new SshClient(new CLISshProxy());
$idx->addLibrary(new Deploy($sshClient, $targets, $sshParams));
$idx->addLibrary(new PHPUnit());
$idx->run();
