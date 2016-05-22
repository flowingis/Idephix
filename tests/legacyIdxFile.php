<?php

use Idephix\Idephix;

$idx = new Idephix(['prod' => []]);

$idx->
add(
    'ping',
    function () use ($idx) {
        echo "pong";
    }
);

$idx->
add(
    'error',
    function () use ($idx) {
        throw new Exception("Error for tests, ignore it :-)");
    }
);

$idx->
add(
    'echo',
    function ($string) use ($idx) {
        echo $string;
    }
);

$idx->run();
