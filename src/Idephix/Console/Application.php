<?php

namespace Idephix\Console;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Command\HelpCommand;
use Idephix\Console\Command\ListCommand;

class Application extends BaseApplication
{
    private $logo = <<<'EOD'

  ___    _            _     _
 |_ _|__| | ___ _ __ | |__ (_)_  __
  | |/ _` |/ _ \  _ \|  _  | \ \/ /
  | | (_| |  __/ |_) | | | | |>  <
 |___\__,_|\___| .__/|_| |_|_/_/\_\
               |_|


EOD;

    private $releaseDate;

    private $output;

    private $input;

    private $tasks;

    public function __construct(
      $name = 'UNKNOWN',
      $version = 'UNKNOWN',
      $releaseDate = 'UNKNOWN',
      $input,
      $output)
    {
        parent::__construct($name, $version);

        $this->input = $input;
        $this->output = $output;
        $this->releaseDate = $releaseDate;

        $this->setAutoExit(false);

        $this->getDefinition()->addOption(new InputOption('--config', 'c', InputOption::VALUE_OPTIONAL, 'idxrc file path', getcwd() . '/' .'idxrc.php'));
        $this->getDefinition()->addOption(new InputOption('--file', 'f', InputOption::VALUE_OPTIONAL, 'idxrc file path', getcwd() . '/' . 'idxfile.php'));
        $this->getDefinition()->addOption(new InputOption('--env', null, InputOption::VALUE_REQUIRED, 'Set remote environment.'));
    }

    public function getHelp()
    {
        return $this->logo . parent::getHelp();
    }

    public function getLongVersion()
    {
        if ('UNKNOWN' === $this->getName() ||
            'UNKNOWN' === $this->getVersion()) {
            return '<info>Console Tool</info>';
        }

        $version = sprintf(
            '<info>%s</info> version <comment>%s</comment> released %s',
            $this->getName(),
            $this->getVersion(),
            $this->releaseDate
        );

        return $version;
    }

    protected function getDefaultCommands()
    {
        return array(new HelpCommand(), new ListCommand());
    }

    public function addTask(Task $task)
    {
        $this->tasks[] = $task;
        $this->add(Command::fromTask($task, $this));

        return $this;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        return $this->tasks->has($name) && $this->has($name);
    }

    public function run($context)
    {
        try {
            $this->buildEnvironment($context->getConfig(), $this->input);
        } catch (\Exception $e) {
            $this->output->writeln('<error>'.$e->getMessage().'</error>');

            return;
        }

        $hasErrors = false;
        foreach ($context->getHosts() as $host) {
            $context->openRemoteConnection($host);
            $returnValue = $this->run($this->input, $this->output);
            $hasErrors = $hasErrors || !(is_null($returnValue) || ($returnValue == 0));
            $context->closeRemoteConnection();
        }

        if ($hasErrors) {
            throw new FailedCommandException();
        }
    }

    public function __call($name, $arguments = array())
    {
        $inputFactory = new InputFactory();

        return $this->get($name)->run(
            $inputFactory->buildFromUserArgsForTask(func_get_args(), $this->tasks->get($name)),
            $this->output
        );
    }

    /**
     * @param InputInterface $input
     * @throws \Exception
     */
    protected function buildEnvironment($context, InputInterface $input)
    {
        $environments = $context->getConfig()->environments();

        $userDefinedEnv = $input->getParameterOption(array('--env'));

        if (false !== $userDefinedEnv && !empty($userDefinedEnv)) {
            if (!isset($environments[$userDefinedEnv])) {
                throw new \Exception(
                    sprintf(
                        'Wrong environment "%s". Available [%s]',
                        $userDefinedEnv,
                        implode(', ', array_keys($environments))
                    )
                );
            }

            $context->setEnv($userDefinedEnv);
        }
    }
}
