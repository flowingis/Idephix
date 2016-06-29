<?php

use Idephix\Idephix;

$idx = new Idephix(array('prod' => array()));

$idx->
add(
    'ping',
    function () use ($idx) {
        echo 'pong';
    }
);

$idx->
add(
    'error',
    function () use ($idx) {
        throw new Exception('Error for tests, ignore it :-)');
    }
);

$idx->
add(
    'echo',
    function ($string) use ($idx) {
        echo $string;
    }
);

$idx->
add(
    'fakedeploy',
    function ($go = false) use ($idx) {
        if ($go) {
            echo 'real-run';
        } else {
            echo 'dry-run';
        }
    }
);


$idx->run();
