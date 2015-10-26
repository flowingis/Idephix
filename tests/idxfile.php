<?php

function _echo($value)
{
    echo $value;
}

function greet($idx, $name)
{
    $idx->runTask('echo', 'Ciao ' . $name);
}

function testParams($param1, $param2, $param3 = 'default')
{
    echo "$param1 $param2 $param3";
}

function aTestIdxFile()
{
    echo 'It is all I am';
}