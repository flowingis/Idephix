<?php


namespace Idephix;

class Dictionary implements DictionaryAccess
{
    private $data = array();

    private function __construct($data)
    {
        $this->data = $data;
    }

    public static function fromArray($data)
    {
        return new static($data);
    }

    public static function dry()
    {
        return new static(array());
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

    private function resolveElement($element)
    {
        if ($element instanceof \Closure) {
            $element = $element();
        }

        return $element;
    }
}
