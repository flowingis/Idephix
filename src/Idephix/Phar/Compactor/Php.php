<?php
namespace Idephix\Phar\Compactor;

use Herrera\Box\Compactor\Php as HerreraPhpCompactor;

class Php extends HerreraPhpCompactor
{
    public function supports($file)
    {
        if (0 === strpos($file, 'src/Idephix/Cookbook/')) {
            return false;
        }

        return parent::supports($file);
    }
}
