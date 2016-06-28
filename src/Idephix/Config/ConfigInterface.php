<?php
namespace Idephix\Config;

interface ConfigInterface
{
    public function getTargets();
    public function getSshClient();
}
