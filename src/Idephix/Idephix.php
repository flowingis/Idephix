<?php

namespace Idephix;

use Idephix\Console\Application;
use Idephix\Console\Command;
use Idephix\Console\InputFactory;
use Idephix\Exception\FailedCommandException;
use Idephix\Exception\MissingMethodException;
use Idephix\Extension\MethodCollection;
use Idephix\Task\Task;
use Idephix\Task\TaskCollection;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Idephix\SSH\SshClient;
use Idephix\Extension\IdephixAwareInterface;
use Idephix\Task\Builtin\SelfUpdate;
use Idephix\Task\Builtin\InitIdxFile;

/**
 * Class Idephix
 * @method InitIdxFile initIdxFile()
 * @method SelfUpdate selfUpdate()
 */
class Idephix implements Builder, TaskExecutor
{
    const VERSION = '@package_version@';
    const RELEASE_DATE = '@release_date@';

    private $application;
    /** @var  TaskCollection */
    private $tasks;

    private $extensionsMethods;
    private $input;
    private $output;
    private $sshClient;
    private $config;
    /** @var  Context */
    protected $context;

    public function __construct(
        Config $config,
        TaskCollection $tasks,
        OutputInterface $output = null,
        InputInterface $input = null)
    {
        $this->config = $config;
        $this->tasks = TaskCollection::dry();
        $this->extensionsMethods = MethodCollection::dry();

        $sshClient = $config['ssh_client'];

        $output = $this->outputOrDefault($output);
        $input = $this->inputOrDefault($input);

        $this->application = new Application(
            'Idephix',
            self::VERSION,
            self::RELEASE_DATE,
            $output,
            $input
        );

        foreach ($tasks as $task) {
            $this->application->addTask($task);
        }

        $this->operations = new Operations($sshClient, $output);
        $this->context = Context::create($this, $config, $this->operations);

        $this->addSelfUpdateCommand();
        $this->addInitIdxFileCommand();

        foreach ($config->extensions() as $extension) {
            $this->addExtension($extension);
        }
    }

    public static function create(TaskCollection $tasks, Config $config, TaskExecutor $executor)
    {
        $idephix = new static($config, $tasks);

        return $idephix;
    }

    public function run()
    {
        $this->application->run($this->context);
    }

    /**
     * @inheritdoc
     */
    public function addExtension(Extension $extension)
    {
        if ($extension instanceof IdephixAwareInterface) {
            $extension->setIdephix($this);
        }

        $this->extensionsMethods = $this->extensionsMethods->merge($extension->methods());

        foreach ($extension->tasks() as $task) {
            if (!$this->has($task->name())) {
                $this->addTask($task);
            }
        }
    }

    public function addSelfUpdateCommand()
    {
        if ('phar:' === substr(__FILE__, 0, 5)) {
            $selfUpdate = new SelfUpdate();
            $selfUpdate->setIdephix($this);
            $this->addTask($selfUpdate);
        }
    }

    public function addInitIdxFileCommand()
    {
        $init = InitIdxFile::fromDeployRecipe();
        $init->setIdephix($this);
        $this->addTask($init);
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
     * @param OutputInterface $output
     * @return ConsoleOutput|OutputInterface
     */
    private function outputOrDefault(OutputInterface $output = null)
    {
        if (null === $output) {
            $output = new ConsoleOutput();
        }

        return $output;
    }

    /**
     * @param InputInterface $input
     * @return ArgvInput|InputInterface
     */
    private function inputOrDefault(InputInterface $input = null)
    {
        $this->removeIdxCustomFileParams();

        if (null === $input) {
            $input = new ArgvInput();
        }

        return $input;
    }
}
