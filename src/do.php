<?php

/**
 *  Controller
 */

require_once __DIR__.'/lib/Deploy.php';
require_once __DIR__.'/lib/SshClient.php';
require_once __DIR__.'/lib/PeclSsh2Proxy.php';

use Ideato\Deploy\Deploy;

if (!is_file(getcwd().'/config.php')) {
    echo getcwd()."/config.php does not exists!\n";

    exit(1);
}

include getcwd().'/config.php';

$argv = $_SERVER['argv'];

if (!isset($argv[1]) || !in_array($argv[1], array_keys($targets))) {
    echo 'Usage: '.$argv[0]." env [bootstrap|deploy]\n";
    echo 'configured env: '.implode(', ', array_keys($targets))."\n";
    exit(1);
}

$target = $targets[$argv[1]];

$sshClient = new Ideato\Deploy\SshClient();
$deploy = new \Ideato\Deploy\Deploy($sshClient, $target, $ssh_params);

if (isset($argv[2]) && 'bootstrap' == $argv[2]) {
    $deploy->bootstrap();
} else {
    $deploy->deploy();
}

