<?php


namespace Idephix\File;

interface IdxFile
{
    public function executionContext();
    
    public function output();

    public function input();

    public function tasks();

    public function libraries();
}
