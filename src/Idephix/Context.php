<?php
namespace Idephix;

class Context extends Dictionary
{
    /**
     * Add trailing slash to the path if it is omitted
     *
     * @param string $name
     * @param string $default
     * @return string fixed path
     */
    public function getAsPath($name, $default = '')
    {
        return rtrim($this->get($name, $default), '/').'/';
    }
}