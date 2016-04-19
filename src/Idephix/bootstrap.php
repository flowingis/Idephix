<?php

namespace Idephix;

use Symfony\Component\Console\Input\ArgvInput;
use Idephix\Application;
use Idephix\Idephix;
use Idephix\File\FunctionBasedIdxFile;

function run()
{
    $input = new ArgvInput();
    $application = new Application('Idephix', Idephix::VERSION);
    $input->bind($application->getDefinition());

    $configFile = $input->getOption('config');
    $idxFile = $input->getOption('file');

    if(!is_file($configFile)){
        $configFile = null;
    }

    if (is_file($idxFile)) {
        if(isLegacyIdxFile($idxFile)){
            return (include $idxFile);
        }

        $idx = Idephix::fromFile(new FunctionBasedIdxFile($idxFile, $configFile));
        return $idx->run();
    }

    if(false === strpos($idxFile, $application->getDefinition()->getOption('file')->getDefault())) {
        echo "$idxFile file not exist!";
        return 1;
    }

    $idx = new Idephix();
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
