<?php

require_once __DIR__.'/../vendor/autoload.php';

$configFile = getcwd().'/idxfile.php';

if (!is_file($configFile)) {
    echo $configFile." does not exists!\n";

    exit(1);
}

include $configFile;
