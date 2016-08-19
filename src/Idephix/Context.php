<?php
namespace Idephix;

class Context implements DictionaryAccess, TaskExecutor, \Iterator
{
    private $idx;
    private $data;

    public function __construct(Dictionary $data, TaskExecutor $idx)
    {
        $this->data = $data;
        $this->idx = $idx;
    }

    public static function dry(TaskExecutor $idx)
    {
        return new static(Dictionary::dry(), $idx);
    }

    public function env($name, Dictionary $contextData)
    {
        $context = clone $this;
        $contextData['env'] = array('name' => $name);
        $context->data = $contextData;

        return $context;
    }

    public function currentEnvName()
    {
        return $this->data['env.name'];
    }

    public function currentHost()
    {
        return $this->data['env.host'];
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

    public function offsetExists($offset)
    {
        return $this->data->offsetExists($offset);
    }

    public function offsetGet($offset)
    {
        return $this->data->offsetGet($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->data->offsetSet($offset, $value);
    }

    public function offsetUnset($offset)
    {
        $this->data->offsetUnset($offset);
    }

    public function get($offset, $default = null)
    {
        return $this->data->get($offset, $default);
    }

    public function set($key, $value)
    {
        $this->data->offsetSet($key, $value);
    }

    /**
     * @param $name
     * @return integer 0 success, 1 fail
     */
    public function execute($name)
    {
        call_user_func_array(array($this->idx, 'execute'), func_get_args());
    }

    /**
     * Execute remote command.
     *
     * @param string $cmd command
     * @param boolean $dryRun
     * @return void
     */
    public function remote($cmd, $dryRun = false)
    {
        $this->idx->remote($cmd, $dryRun);
    }

    /**
     * Execute local command.
     *
     * @param string $cmd Command
     * @param boolean $dryRun
     * @param integer $timeout
     *
     * @return string the command output
     */
    public function local($cmd, $dryRun = false, $timeout = 60)
    {
        return $this->idx->local($cmd, $dryRun, $timeout);
    }

    public function output()
    {
        return $this->idx->output();
    }

    public function write($messages, $newline = false, $type = self::OUTPUT_NORMAL)
    {
        $this->idx->write($messages, $newline, $type);
    }

    public function writeln($messages, $type = self::OUTPUT_NORMAL)
    {
        $this->idx->writeln($messages, $type);
    }

    public function sshClient()
    {
        return $this->idx->sshClient();
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array(array($this->idx, $name), $arguments);
    }

    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current()
    {
        $newContextData = clone $this->data;
        $newContextData['env'] = array(
            'name' => $this->currentEnvName(),
            'host' => current($this->hosts)
        );

        $newContext = new static($newContextData, $this->idx);

        return $newContext;
    }

    /**
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next()
    {
        next($this->hosts);
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key()
    {
        return key($this->hosts);
    }

    /**
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid()
    {
        return $this->key() !== null;
    }

    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind()
    {
        $this->hosts = $this->data->get('hosts', array(null));
        reset($this->hosts);
    }
}
