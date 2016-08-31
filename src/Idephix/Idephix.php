<?php

namespace Idephix;

use Idephix\Console\Application;
use Idephix\Extension\MethodCollection;
use Idephix\Extension\TaskProvider;
use Idephix\Extension\MethodProvider;
use Idephix\Task\TaskCollection;
use Idephix\Task\Task;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Idephix\Extension\IdephixAwareInterface;

use Idephix\Task\Builtin\SelfUpdate;
use Idephix\Task\Builtin\InitIdxFile;

class Idephix implements Builder
{
    const VERSION = '@package_version@';
    const RELEASE_DATE = '@release_date@';

    private $executor;

    private $extensionsMethods;

    private $config;

    protected $context;

    public function __construct(
        Config $config,
        TaskCollection $tasks,
        OutputInterface $output = null,
        InputInterface $input = null)
    {
        $output = $this->outputOrDefault($output);
        $input = $this->inputOrDefault($input);

        $this->config = $config;
        $this->extensionsMethods = MethodCollection::dry();
        $operations = new Operations($config['ssh_client'], $output);

        $this->executor = new Application(
            'Idephix',
            self::VERSION,
            self::RELEASE_DATE,
            $input,
            $output
        );

        $this->context = new Context($this->executor, $operations, $config);

        $this->addSelfUpdateCommand($this->context);
        $this->addInitIdxFileCommand($this->context);

        foreach ($tasks as $task) {
            $this->addTask($task);
        }

        foreach ($config->extensions() as $extension) {
            $this->addExtension($extension, $this->context);
        }
    }

    public function run()
    {
        $this->executor->runContext($this->context);
    }

    public function addTask(Task $task)
    {
        $this->executor->addTask($task, $this->context);
    }

    public function addExtension(Extension $extension)
    {
        if ($extension instanceof ContextAwareInterface) {
            $extension->setContext($this->context);
        }

        if ($extension instanceof TaskProvider) {
            foreach ($extension->tasks() as $task) {
                $this->addTask($task, $this->context);
            }
        }

        if ($extension instanceof MethodProvider) {
            $this->operations->addMethods($extension->methods());
        }
    }

    public function addSelfUpdateCommand($ctx)
    {
        if ('phar:' === substr(__FILE__, 0, 5)) {
            $selfUpdate = new SelfUpdate();
            $selfUpdate->setContext($ctx);

            $this->executor->addTask($selfUpdate, $ctx);
        }
    }

    public function addInitIdxFileCommand($ctx)
    {
        $init = InitIdxFile::fromDeployRecipe();
        $init->setContext($ctx);

        $this->executor->addTask($init, $ctx);
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
