<?php
namespace Ideato\Deploy;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputArgument;

class Idephix
{
    private $application;
    private $library = array();

    public function __construct()
    {
        $this->application = new Application();
        $this->application->add(new DeployCommand());
    }

    /**
     * @todo come facciamo i parametri tipo "--go"? Con convention? Tipo se il nome è flag_* allora...
     * @param $name
     * @param Closure $code
     */
    public function add($name, \Closure $code)
    {
        $command = new Command($name);
        $command->setCode($code);

        $reflector = new \ReflectionFunction($code);

        if (preg_match('/\s*\*\s*@[Dd]escription(.*)/', $reflector->getDocComment(), $matches)) {
            $command->setDescription(trim($matches[1], '*/ '));
        }
        foreach ($reflector->getParameters() as $parameter) {
            if ($parameter->isOptional()) {
                $command->addArgument($parameter->getName(), InputArgument::OPTIONAL, '', $parameter->getDefaultValue());
            } else {
                $command->addArgument($parameter->getName(), InputArgument::REQUIRED);
            }
//            ->addOption('yell', null, InputOption::VALUE_NONE, 'If set, the task will yell in uppercase letters')
        }

        $this->application->add($command);

        return $this;
    }

    public function run()
    {
        $this->application->run();
    }

    public function addLibrary($library)
    {
        $this->library[] = $library;
    }

    public function __call($name, $arguments)
    {
        foreach ($this->library as $library) {
            if (is_callable(array($library, $name))) {
                call_user_func_array(array($library, $name), $arguments);
                break;
            }
        }
    }
}
