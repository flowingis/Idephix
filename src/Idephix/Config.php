<?php
namespace Idephix;

use Idephix\Exception\InvalidConfigurationException;

class Config extends Dictionary
{
    public static function parseFile($configFile)
    {
        if(is_null($configFile)){
            return static::dry();
        }
        
        try {
            new \SplFileObject($configFile);
        } catch (\RuntimeException $e) {
            throw new InvalidConfigurationException('The config file does not exists or is not readable');
        }

        /** @var Config $config */
        $config = require_once $configFile;

        if(!$config instanceof Config){
            throw new InvalidConfigurationException('The config must be an instance of Idephix\Config');
        }

        return $config;
    }
}
