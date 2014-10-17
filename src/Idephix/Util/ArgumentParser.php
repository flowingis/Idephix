<?php

namespace Idephix\Util;

class ArgumentParser
{
    protected $params;
    protected $options;

    private function addLongOption($param)
    {
        if (!preg_match('/^--([^=]*)=?(.*)$/', $param, $matches)) {
            throw new \Exception("Invalid long parameter: ".$param);
        }

        $value = empty($matches[2]) ? true : $matches[2];
        $this->options[$matches[1]] = $value;
    }

    private function addShortOptions($param)
    {
        if (!preg_match('/^-([^-]*)$/', $param, $options)) {
            throw new \Exception("Invalid short parameters: ".$param);
        }

        foreach (str_split($options[1]) as $option) {
            $this->options[(string) $option] = true;
        }
    }

    private function isOnlyParamsDelimiter($argument)
    {
        return $argument == '--';
    }

    /**
     * Parses array of arguments
     *
     * Supports:
     * -a
     * -b<value> [not yet]
     * -b <value> [not yet]
     * -cd
     * --long-param
     * --long-param=<value>
     * <value>
     *
     * @param string[] $arguments
     * @param array $noOptions List of parameters without values
     */
    public function parse($arguments, $noOptions = array())
    {
        $this->options = array();
        $this->params = array();
        $onlyParams = false;

        foreach ($arguments as $p) {

            if ($this->isOnlyParamsDelimiter($p)) {
                $onlyParams = true;

                continue;
            }

            if ($onlyParams) {
                $this->params[] = $p;

                continue;
            }

            if (preg_match('/^--[^-]*/', $p)) {
                $this->addLongOption($p);
            } elseif (preg_match('/^-[^-]*/', $p)) {
                $this->addShortOptions($p);
            } else {
                $this->params[] = $p;
            }
                // check if next parameter is a descriptor or a value
/*                $nextparm = current($arguments);
                if (!in_array($pname, $noOptions)
                    && $value === true
                    && $nextparm !== false
                    && $nextparm{0} != '-') {

                    list($tmp, $value) = each($arguments);
                } */
        }
    }

    public function getOption($name)
    {
        return isset($this->options[$name]) ? $this->options[$name] : null;
    }

    public function getParam($name)
    {
        return isset($this->params[$name]) ? $this->params[$name] : null;
    }

    public function getParams()
    {
        return $this->params;
    }
}
