<?php
namespace Idephix\Task;

use Idephix\Task\Parameter\Collection;
use Idephix\Task\Parameter\Idephix;
use Idephix\Task\Parameter\UserDefined;
use Idephix\Task\Parameter\UserDefinedCollection;
use Idephix\Util\DocBlockParser;

class CallableTask implements Task
{
    private $name;
    private $description;
    private $parameters;
    private $code;

    public function __construct($name, $description, $code, Collection $parameters)
    {
        $this->name = $name;
        $this->description = $description;
        $this->parameters = $parameters;
        $this->code = $code;
    }

    /**
     * @return static
     */
    public static function buildFromClosure($name, \Closure $code)
    {
        $parameters = Collection::dry();

        $reflector = new \ReflectionFunction($code);
        $parser = new DocBlockParser($reflector->getDocComment());

        foreach ($reflector->getParameters() as $parameter) {
            if ($parameter->getClass() && $parameter->getClass()->implementsInterface('\Idephix\IdephixInterface')) {
                $parameters[] = Idephix::create();
                continue;
            }

            $description = $parser->getParamDescription($parameter->getName());
            $default = $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null;
            $parameters[] = UserDefined::create($parameter->getName(), $description, $default);
        }

        return new CallableTask(str_replace('_', '', $name), $parser->getDescription(), $code, $parameters);
    }

    public function name()
    {
        return $this->name;
    }

    public function description()
    {
        return $this->description;
    }

    /**
     * @return Collection
     */
    public function parameters()
    {
        return $this->parameters;
    }

    public function userDefinedParameters()
    {
        return new UserDefinedCollection($this->parameters);
    }

    public function code()
    {
        return $this->code;
    }

    public static function dummy()
    {
        $code = function ($bar) { echo $bar; };
        $params = Collection::createFromArray(array('bar'=> array('description' => '')));

        return new static('foo', 'foo descr', $code, $params);
    }
}
