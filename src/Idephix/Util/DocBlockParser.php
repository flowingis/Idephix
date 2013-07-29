<?php

namespace Idephix\Util;

/**
 * Parse a docblock comment finding global description
 * and parameters description
 */
class DocBlockParser
{
    private $description;
    private $params;

    /**
     * @param string $comment the docBlock to parse
     */
    public function __construct($comment)
    {
        $this->parse($comment);
    }

    /**
     * Parse the string looking for description and parameters
     *
     * @param string $comment the docBlock to parse
     */
    private function parse($comment)
    {
        $rows = explode("\n", preg_replace('!(^\s*/\*\*\s*)|(\s*\*/\s*$)!', '', $comment));
        $this->description = $this->parseDescription($rows);
        $this->params = $this->parseParams($rows);
    }

    /**
     * Parse an array of string looking for parameters
     *
     * @param array $rows the docBlock to parse
     *
     * @return array parameters name, type and description
     */
    private function parseParams($rows)
    {
        $params = array();

        foreach ($rows as $row) {
            if (preg_match_all('/^\s*\*\s*@param\s*(?<type>[^\s]*)(?:\s*\$(?<name>[^\s]*))(?:\s*(?<description>.*))?$/us',
                $row,
                $matches
            )) {
                $name = $matches['name'][0];
                $params[$name] = array(
                    'name' => $name,
                    'type' => $matches['type'][0],
                    'description' => $matches['description'][0],
                );
            }

        }

        return $params;
    }

    /**
     * Parse an array of string looking for global description
     *
     * @param array $rows the docBlock to parse
     *
     * @return string global description
     */
    private function parseDescription($rows)
    {
        $description = array();
        $row = '';
        while ((list(, $row) = each($rows)) && (false === strpos($row, '@'))) {
            $parts = trim($row, ' *');
            if (!empty($parts)) {
                $description[] = $parts;
            }
        }

        return implode(' ', $description);
    }

    /**
     * If available, returns the param type, name and description
     *
     * @param string $name the param name
     *
     * @return array|null the param type, name and description
     */
    public function getParam($name)
    {
        return $this->hasParam($name) ? $this->params[$name] : null;
    }

    public function hasParam($name)
    {
        return isset($this->params[$name]);
    }

    public function getParams()
    {
        return $this->params;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getParamDescription($name)
    {
        return $this->hasParam($name) ? $this->params[$name]['description'] : '';
    }
}
