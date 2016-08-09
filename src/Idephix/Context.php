<?php
namespace Idephix;

class Context implements \ArrayAccess
{
    private $idx;
    private $data = array();

    private function __construct($data, TaskExecutor $idx)
    {
        $this->data = $data;
        $this->idx = $idx;
    }

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

    public static function fromArray($data, TaskExecutor $idx)
    {
        return new static($data, $idx);
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->data);
    }

    public function offsetGet($offset)
    {
        if (array_key_exists($offset, $this->data)) {
            return $this->resolveElement($this->data[$offset]);
        }

        $name = explode('.', $offset);

        $element = $this->data;

        foreach ($name as $i => $part) {
            if (!isset($element[$part])) {
                return null;
            }

            $element = $element[$part];
        }

        return $this->resolveElement($element);
    }

    public function offsetSet($offset, $value)
    {
        if (isset($this->data[$offset])) {
            $this->data[$offset] = $value;
            return;
        }

        $offset = array_reverse(explode('.', $offset));

        $result = $value;

        foreach ($offset as $part) {
            $result = array($part => $result);
        }

        $this->data = array_replace_recursive($this->data, $result);
    }

    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    public function get($offset, $default = null)
    {
        if (is_null($this->offsetGet($offset))) {
            return $default;
        }

        return $this->offsetGet($offset);
    }

    public function set($key, $value)
    {
        $this->offsetSet($key, $value);
    }

    /**
     * @param $element
     * @return mixed
     */
    private function resolveElement($element)
    {
        if ($element instanceof \Closure) {
            $element = $element();
        }

        return $element;
    }
}
