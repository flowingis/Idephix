<?php

namespace Idephix;

use Idephix\Console\Application;
use Idephix\Console\Command;
use Idephix\Exception\DeprecatedException;
use Idephix\Exception\FailedCommandException;
use Idephix\Exception\InvalidTaskException;
use Idephix\Task\Task;
use Idephix\Task\TaskCollection;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Idephix\SSH\SshClient;
use Idephix\Extension\IdephixAwareInterface;
use Idephix\Extension\SelfUpdate\SelfUpdate;
use Idephix\Extension\InitIdxFile\InitIdxFile;

/**
 * Class Idephix
 * @method InitIdxFile initIdxFile()
 * @method SelfUpdate selfUpdate()
 */
class Idephix implements IdephixInterface
{
    const VERSION = '@package_version@';
    const RELEASE_DATE = '@release_date@';

    private $application;
    private $extensions = array();
    private $input;
    private $output;
    private $sshClient;
    private $targets = array();
    /** @var  Config */
    protected $currentTarget;
    protected $currentTargetName;
    protected $currentHost;
    protected $invokerClassName;

    public function __construct(
        $config,
        OutputInterface $output = null,
        InputInterface $input = null)
    {
        if (!$config instanceof Config) {
            throw new DeprecatedException("You're using an old idxfile format, consider updating. http://idephix.readthedocs.io/en/latest/migrating_idx_file.html");
        }

        $this->application = new Application(
            'Idephix',
            self::VERSION,
            self::RELEASE_DATE
            );

        $this->targets = $config->targets();
        $sshClient = $config['sshClient'];

        if (null === $sshClient) {
            $sshClient = new SshClient();
        }
        $this->sshClient = $sshClient;

        if (null === $output) {
            $output = new ConsoleOutput();
        }
        $this->output = $output;

        $this->removeIdxCustomFileParams();

        if (null === $input) {
            $input = new ArgvInput();
        }

        $this->input = $input;
        $this->addSelfUpdateCommand();
        $this->addInitIdxFileCommand();
        
        foreach ($config->extensions() as $extension) {
            $this->addExtension($extension);
        }
    }

    public static function create(TaskCollection $tasks, Config $config)
    {
        $idephix = new static($config);

        foreach ($tasks as $task) {
            $idephix->application->add(Command::fromTask($task, $idephix));
        }

        return $idephix;
    }

    public function output()
    {
        return $this->output;
    }

    public function input()
    {
        return $this->input();
    }

    public function __call($name, $arguments = array())
    {
        if ($this->has($name)) {
            return call_user_func_array(array($this, 'runTask'), array_merge(array($name), $arguments));
        }

        foreach ($this->extensions as $libName => $extension) {
            if (is_callable(array($extension, $name))) {
                return call_user_func_array(array($extension, $name), $arguments);
            }
        }

        throw new \BadMethodCallException('Call to undefined method: "'.$name.'"');
    }

    public function __get($name)
    {
        if ($name === 'output' || $name === 'sshClient') {
            return $this->$name;
        }

        $trace = debug_backtrace();
        trigger_error(
            'Undefined property: '.$name.
            ' in '.$trace[0]['file'].
            ' on line '.$trace[0]['line'],
            E_USER_NOTICE
        );

        return null;
    }

    /**
     * @inheritdoc
     */
    public function add($task, $code = null)
    {
        if ($task instanceof Task) {
            $this->application->add(Command::fromTask($task, $this));
            return $this;
        }

        if (is_string($task) && is_callable($code)) {
            $this->application->add(Command::buildFromCode($task, $code, $this));
            return $this;
        }

        throw new InvalidTaskException('A task must be an instance of Idephix\Task or Callable');
    }

    /**
     * @param InputInterface $input
     * @throws \Exception
     */
    protected function buildEnvironment(InputInterface $input)
    {
        $this->currentTarget = null;
        $this->currentTargetName = null;
        $env = $input->getParameterOption(array('--env'));
        if (false !== $env && !empty($env)) {
            if (!isset($this->targets[$env])) {
                throw new \Exception(
                    sprintf(
                        'Wrong environment "%s". Available [%s]',
                        $env,
                        implode(', ', array_keys($this->targets))
                    )
                );
            }

            $this->currentTarget = Context::fromArray(
                array_merge(
                    array('hosts' => array()),
                    $this->targets[$env]
                )
            );
            $this->currentTargetName = $env;
        }
    }

    protected function hasTarget()
    {
        return null !== $this->currentTarget;
    }

    protected function openRemoteConnection($host)
    {
        if ($this->hasTarget()) {
            $this->sshClient->setParameters($this->currentTarget['ssh_params']);
            $this->sshClient->setHost($host);
            $this->sshClient->connect();
        }
    }

    protected function closeRemoteConnection()
    {
        if ($this->hasTarget()) {
            $this->sshClient->disconnect();
        }
    }

    public function getCurrentTarget()
    {
        return $this->currentTarget;
    }

    public function getCurrentTargetHost()
    {
        return $this->currentHost;
    }

    public function getCurrentTargetName()
    {
        return $this->currentTargetName;
    }

    public function run()
    {
        try {
            $this->buildEnvironment($this->input);
        } catch (\Exception $e) {
            $this->output->writeln('<error>'.$e->getMessage().'</error>');

            return;
        }

        $hosts = $this->hasTarget() ? $this->currentTarget['hosts'] : array(null);

        $hasErrors = false;
        foreach ($hosts as $host) {
            $this->currentHost = $host;
            $this->openRemoteConnection($host);
            $returnValue = $this->application->run($this->input, $this->output);
            $hasErrors = $hasErrors || !(is_null($returnValue) || ($returnValue == 0));
            $this->closeRemoteConnection();
        }

        if ($hasErrors) {
            throw new FailedCommandException();
        }
    }

    /**
     * @inheritdoc
     */
    public function addExtension(Extension $extension)
    {
        if ($extension instanceof IdephixAwareInterface) {
            $extension->setIdephix($this);
        }

        $this->extensions[$extension->name()] = $extension;

        foreach ($extension->tasks() as $task) {
            if (!$this->has($task->name())) {
                $this->application->add(Command::fromTask($task, $this));
            }
        }
    }

    /**
     * @param $name
     * @param $extension
     * @deprecated should use addExtension instead
     */
    public function addLibrary($name, $extension)
    {
        $this->addExtension($name, $extension);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        return $this->application->has($name);
    }

    /**
     * RunTask.
     *
     * @param string $name the name of the task you want to call
     * @param (...)  arbitrary number of parameter matching the target task interface
     * @return integer
     * @deprecated should call directly tasks as Idephix methods 
     */
    public function runTask($name)
    {
        if (!$this->has($name)) {
            throw new \InvalidArgumentException(sprintf('The command "%s" does not exist.', $name));
        }

        $arguments = new ArgvInput(array_merge(array('exec_placeholder'), func_get_args()));

        return $this->application->get($name)->run($arguments, $this->output);
    }

    public function addSelfUpdateCommand()
    {
        if ('phar:' === substr(__FILE__, 0, 5)) {
            $this->addExtension(new SelfUpdate());
        }
    }

    public function addInitIdxFileCommand()
    {
        $this->addExtension(new InitIdxFile());
    }

    /**
     * Execute remote command.
     *
     * @param string $cmd command
     * @param boolean $dryRun
     * @throws \Exception
     */
    public function remote($cmd, $dryRun = false)
    {
        if (!$this->sshClient->isConnected()) {
            throw new \Exception('Remote function need a valid environment. Specify --env parameter.');
        }
        $this->output->writeln('<info>Remote</info>: '.$cmd);

        if (!$dryRun && !$this->sshClient->exec($cmd)) {
            throw new \Exception('Remote command fail: '.$this->sshClient->getLastError());
        }
        $this->output->writeln($this->sshClient->getLastOutput());
    }

    /**
     * Execute local command.
     *
     * @param string $cmd Command
     * @param bool $dryRun
     * @param int $timeout
     *
     * @return string the command output
     * @throws \Exception
     */
    public function local($cmd, $dryRun = false, $timeout = 600)
    {
        $output = $this->output;
        $output->writeln("<info>Local</info>: $cmd");

        if ($dryRun) {
            return $cmd;
        }

        $process = $this->buildInvoker($cmd, null, null, null, $timeout);

        $result = $process->run(function ($type, $buffer) use ($output) {
            $output->write($buffer);
        });
        if (0 != $result) {
            throw new \Exception('Local command fail: '.$process->getErrorOutput());
        }

        return $process->getOutput();
    }

    /**
     * Set local command invoker
     * @param string $invokerClassName class name of the local command invoker
     */
    public function setInvoker($invokerClassName)
    {
        $this->invokerClassName = $invokerClassName;
    }

    /**
     * Build command invoker
     * @param string  $cmd     The command line to run
     * @param string  $cwd     The working directory
     * @param array   $env     The environment variables or null to inherit
     * @param string  $stdin   The STDIN content
     * @param integer $timeout The timeout in seconds
     * @param array   $options An array of options for proc_open
     *
     * @return string cmd output
     */
    public function buildInvoker($cmd, $cwd = null, array $env = null, $stdin = null, $timeout = 60, array $options = array())
    {
        $invoker = $this->invokerClassName ?: '\Symfony\Component\Process\Process';

        return new $invoker($cmd, $cwd, $env, $stdin, $timeout, $options);
    }

    /**
     * Get application
     *
     * @return Application
     */
    public function getApplication()
    {
        return $this->application;
    }

    protected function removeIdxCustomFileParams()
    {
        while ($argument = current($_SERVER['argv'])) {
            if ($argument == '-f' || $argument == '--file' || $argument == '-c' || $argument == '--config') {
                unset($_SERVER['argv'][key($_SERVER['argv'])]);
                unset($_SERVER['argv'][key($_SERVER['argv'])]);
                reset($_SERVER['argv']);
            }

            next($_SERVER['argv']);
        }
    }

    /**
     * @return SshClient
     */
    public function sshClient()
    {
        return $this->sshClient;
    }
}
