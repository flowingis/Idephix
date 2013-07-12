<?php

require_once __DIR__.'/../vendor/autoload.php';

$configFile = getcwd().'/idxfile.php';
if (!is_file($configFile)) {
    $idx = new Idephix\Idephix();
    $idx->output->writeln("<error>idephix.php file does not exists!</error>");

    $dialog = new \Symfony\Component\Console\Helper\DialogHelper();
    if (!$dialog->askConfirmation(
        $idx->output,
        '<question>Do you want that I generate one for you? [yes|no] </question>',
        function () {
            if ('yes' === $answer) {
                return true;
            }
            return false;
        })
    ) {
        return;
    }

    try {
        $idx->initIdxFile()->initFile();
    } catch (\Exception $e) {
        $idx->output->writeln("<error>".$e->getMessage()."</error>");
        exit();
    }
}

include $configFile;