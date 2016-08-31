<?php

namespace Idephix;

use Idephix\Exception\FailedCommandException;
use Idephix\Task\TaskCollection;
use Symfony\Component\Console\Input\ArgvInput;

function run()
{
    $input = new ArgvInput();

    $configFile = $input->getParameterOption(array('--config', '-c'), getcwd() . '/' .'idxrc.php');
    $defaultIdxFile = getcwd() . '/' . 'idxfile.php';
    $idxFile = $input->getParameterOption(array('--file', '-f'), $defaultIdxFile);

    if (!is_file($configFile)) {
        $configFile = null;
    }

    if (is_file($idxFile)) {
        try {
            $idx = new Idephix(
                Config::parseFile($configFile),
                TaskCollection::parseFile($idxFile)
            );

            $idx->run();

            return 0;
        } catch (FailedCommandException $e) {
            return 1;
        }
    }

    if (false === strpos($idxFile, $defaultIdxFile)) {
        echo "$idxFile file not exist!";
        return 1;
    }

    $idx = new Idephix(Config::dry(), TaskCollection::dry());

    return $idx->run();
}
