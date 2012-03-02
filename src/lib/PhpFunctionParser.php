<?php

namespace Ideato\Deploy;

class PhpFunctionParser
{
    public $functions;

    public function __construct($code)
    {
        $this->functions = $this->parse($code);
    }

    private function parseFunction($code)
    {
        preg_match_all('/function\s+([^\(]*?\([^\)]*?\))/mi', $code, $matches);

        return $matches;
    }

    private function parseParams($code)
    {
        $params = array();
        foreach (explode(',', $code) as $param_string) {
            $default = '';
            if (strpos($param_string, '=') !== false) {
              list($param_string, $default) = explode('=', $param_string);
            }
            $params[] = array(
                              'name' => trim($param_string),
                              'required' => (bool)(trim($default) == ''),
                              'default' => trim($default)
                             );
        }

        return $params;
    }

    private function parse($code)
    {
        $functions_decription = array();
        $functions_parsed = $this->parseFunction($code);

        if (empty($functions_parsed))
        {
            return array();
        }

        foreach ($functions_parsed[1] as $match) {
            if (!preg_match_all('/(?<name>[^\s\(]+)\s*\((?<params>[^\)]*)\)/mi', $match, $function))
            {
                throw new \Exception('Parsing file failed');
            }

            $params = $this->parseParams($function['params'][0]);
            $functions_decription[] = array('name' => $function['name'][0],
                                            'params' => $params);
//            var_dump($function);
        }

        return $functions_decription;
    }

    public function getFunctions()
    {
        return $this->functions;
    }
}