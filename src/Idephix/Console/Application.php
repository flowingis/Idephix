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

    public function __construct(
      $name = 'UNKNOWN',
      $version = 'UNKNOWN',
      $releaseDate = 'UNKNOWN')
    {
        parent::__construct($name, $version);

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
}
