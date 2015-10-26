<?php

use Idephix\Idephix;

$idx = new Idephix(['prod' => []]);

$idx->
add(
    'echo',
    function ($string) use ($idx) {
        echo $string;
    }
);

$idx->run();