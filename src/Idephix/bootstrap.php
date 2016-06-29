<?php

namespace Idephix;

use Symfony\Component\Console\Input\ArgvInput;
use Idephix\File\FunctionBasedIdxFile;

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
            if (isLegacyIdxFile($idxFile)) {
                include $idxFile;
            } else {
                $idx = Idephix::fromFile(new FunctionBasedIdxFile($idxFile, $configFile));
                $idx->run();
            }
            return 0;
        } catch (FailedCommandException $e) {
            return 1;
        }
    }

    if (false === strpos($idxFile, $defaultIdxFile)) {
        echo "$idxFile file not exist!";
        return 1;
    }

    $idx = new Idephix(Config::dry());
    return $idx->run();
}

/**
 * @param $idxFile
 * @return int
 */
function isLegacyIdxFile($idxFile)
{
    return preg_match('/new\sIdephix(\(|\\\\)/', file_get_contents($idxFile), $matches);
}
