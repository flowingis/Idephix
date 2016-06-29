<?php
namespace Idephix;

abstract class Dictionary implements \ArrayAccess
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

    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->data);
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        if(array_key_exists($offset, $this->data)){
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

    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
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

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    public function get($offset, $default = null)
    {
        if(is_null($this->offsetGet($offset))){
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
        if($element instanceof \Closure){
            $element = $element();
        }

        return $element;
    }
}