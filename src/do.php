<?php

/**
 *  Controller
 */

require_once __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/lib/CommandWrapper.php';
require_once __DIR__.'/lib/Idephix.php';
require_once __DIR__.'/lib/Deploy.php';
require_once __DIR__.'/lib/SshClient.php';
require_once __DIR__.'/lib/CLISshProxy.php';
require_once __DIR__.'/lib/PhpFunctionParser.php';

use Ideato\Deploy\PhpFunctionParser;
use Ideato\Deploy\Deploy;
use Ideato\Deploy\SshClient;
use Ideato\Deploy\CLISshProxy;

use Ideato\Deploy\Idephix;

$idx = new Idephix();

$configFile = getcwd().'/idxfile.php';

if (!is_file($configFile)) {
    echo $configFile." does not exists!\n";

    exit(1);
}

include $configFile;

$sshClient = new SshClient(new CLISshProxy());
$idx->addLibrary(new Deploy($sshClient, $targets, $sshParams));
$idx->run();
