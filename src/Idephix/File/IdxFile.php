<?php


namespace Idephix\File;


interface IdxFile
{
    public function targets();

    public function sshClient();

    public function output();

    public function input();

    public function tasks();

    public function libraries();
}