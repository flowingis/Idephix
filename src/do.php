<?php

/**
 *  Controller
 */

require_once __DIR__.'/lib/Deploy.php';
require_once __DIR__.'/lib/SshClient.php';
require_once __DIR__.'/lib/CLISshProxy.php';
require_once __DIR__.'/lib/PhpFunctionParser.php';

use Ideato\Deploy\PhpFunctionParser;
use Ideato\Deploy\Deploy;
use Ideato\Deploy\SshClient;
use Ideato\Deploy\CLISshProxy;

$configFile = getcwd().'/idxfile.php';

if (!is_file($configFile)) {
    echo $configFile." does not exists!\n";

    exit(1);
}

include $configFile;

$argv = $_SERVER['argv'];
$parser = new PhpFunctionParser(\file_get_contents($configFile));
$functions = $parser->getFunctions();
$functions = \array_reduce($functions, function($r, $v) { $r[] = $v['name']; return $r; });

if (!isset($argv[1])) {
    echo 'Usage: '.$argv[0]." [".implode("|", $functions)."]\n";
    echo 'configured env: '.implode(', ', array_keys($targets))."\n";
    exit(1);
}

$sshClient = new SshClient(new CLISshProxy());
$deploy = new Deploy($sshClient, $targets, $ssh_params);

array_shift($argv);
$deploy->callback($argv);
