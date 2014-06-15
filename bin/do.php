<?php

require_once __DIR__.'/../vendor/autoload.php';

$configFile = getcwd().'/idxfile.php';
if (is_file($configFile)){
    include $configFile;
    return;
}

$configFile = getcwd().'/idxfile.php.dist';
if (is_file($configFile)){
    include $configFile;
    return;
}

$idx = new Idephix\Idephix();
$idx->run();
