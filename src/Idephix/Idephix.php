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

class Idephix implements Builder
{
    const VERSION = '@package_version@';
    const RELEASE_DATE = '@release_date@';

    private $application;

    private $extensionsMethods;
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

        $output = $this->outputOrDefault($output);
        $input = $this->inputOrDefault($input);

        $this->application = new Application(
            'Idephix',
            self::VERSION,
            self::RELEASE_DATE,
            $output,
            $input
        );

        $this->addSelfUpdateCommand();
        $this->addInitIdxFileCommand();

        foreach ($tasks as $task) {
            $this->application->addTask($task);
        }

        $operations = new Operations($config['ssh_client'], $output);
        $this->context = Context::create($this->application, $config, $operations);

        foreach ($config->extensions() as $extension) {
            $this->addExtension($extension);
        }
    }

    public static function create(TaskCollection $tasks, Config $config)
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
            $this->application->addTask($selfUpdate);
        }
    }

    public function addInitIdxFileCommand()
    {
        $init = InitIdxFile::fromDeployRecipe();
        $init->setIdephix($this);
        $this->application->addTask($init);
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
}
