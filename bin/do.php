<?php

const DEFAULT_IDXFILE = 'idxfile.php';

require_once __DIR__ . '/../vendor/autoload.php';

$idxFilePath = getopt("f:", array("file:"));

$configFile = getcwd() . '/' . DEFAULT_IDXFILE;

if (isset($idxFilePath["f"])) {
    $configFile = $idxFilePath["f"];
} elseif (isset($idxFilePath["file"])) {
    $configFile = $idxFilePath["file"];
}

if (is_file($configFile)) {
    include $configFile;

    return;
}

if(false === strpos($configFile, DEFAULT_IDXFILE)) {
    echo "$configFile file not exist!";
    exit;
}

$idx = new Idephix\Idephix();
$idx->run();
