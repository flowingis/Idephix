<?php
namespace Idephix;

class Context implements DictionaryAccess, TaskExecutor
{
    private $idx;
    private $targetData;
    private $targetName;

    private function __construct(Dictionary $data, TaskExecutor $idx)
    {
        $this->targetData = $data;
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

    public static function dry(TaskExecutor $idx)
    {
        return new static(Dictionary::dry(), $idx);
    }

    public static function currentTarget($name, Dictionary $targetData, TaskExecutor $idx)
    {
        $targetData['target'] = array('name' => $name);

        return new static($targetData, $idx);
    }

    public static function currentHost($name, $host, $targetData, TaskExecutor $idx)
    {
        $targetData['target'] = array('name' => $name, 'host' => $host);

        return new static($targetData, $idx);
    }
    public function offsetExists($offset)
    {
        return $this->targetData->offsetExists($offset);
    }

    public function offsetGet($offset)
    {
        return $this->targetData->offsetGet($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->targetData->offsetSet($offset, $value);
    }

    public function offsetUnset($offset)
    {
        $this->targetData->offsetUnset($offset);
    }

    public function get($offset, $default = null)
    {
        return $this->targetData->get($offset, $default);
    }

    public function set($key, $value)
    {
        $this->targetData->offsetSet($key, $value);
    }

    /**
     * @param $name
     * @return integer 0 success, 1 fail
     */
    public function runTask($name)
    {
        call_user_func_array(array($this->idx, 'runTask'), func_get_args());
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

    /**
     * @return null|Context
     * @deprecated
     */
    public function getContext()
    {
        return $this->idx->getContext();
    }

    /**
     * @return mixed
     * @deprecated
     */
    public function getCurrentTargetHost()
    {
        return $this->idx->getCurrentTargetHost();
    }

    /**
     * @return mixed
     * @deprecated
     */
    public function getCurrentTargetName()
    {
        return $this->idx->getCurrentTargetName();
    }
}
