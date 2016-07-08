<?php
namespace Idephix\Task\Parsing;

use Idephix\Task\Parameter;
use Idephix\Task\ParameterCollection;
use Idephix\Task\Task;
use Idephix\Task\TaskCollection;
use Idephix\Util\DocBlockParser;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\PrettyPrinter\Standard;

class IdxTaskVisitor extends NodeVisitorAbstract
{
    /**
     * @var TaskCollection
     */
    private $collection;

    /**
     * @var Standard
     */
    private $codePrinter;

    public function __construct(TaskCollection $collection)
    {
        $this->collection = $collection;
        $this->codePrinter = new Standard();
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Function_) {
            $closureName = 'task';
            $closure = $this->convertFunctionToClosure($node, $closureName);

            eval($this->codePrinter->prettyPrint(array($closure)));
            $code = ${$closureName};

            $reflector = new \ReflectionFunction($code);
            $parser = new DocBlockParser($reflector->getDocComment());

            $parameters = ParameterCollection::dry();

            foreach ($reflector->getParameters() as $parameter) {
                if ($parameter->getName() !== 'idx') {
                    $description = $parser->getParamDescription($parameter->getName());
                    $parameters[] = Parameter::create(
                        $parameter->getName(),
                        $description,
                        $this->getDefaultValue($parameter)
                    );
                }
            }

            $this->collection[] = new Task(
                $this->cleanupTaskName($node), $parser->getDescription(), $code, $parameters
            );
        }
    }

    /**
     * @param Node $node
     * @return mixed
     */
    private function cleanupTaskName(Node $node)
    {
        return str_replace('_', '', $node->name);
    }

    /**
     * @param Node $functionNode
     * @return Node\Expr\Assign
     */
    private function convertFunctionToClosure(Node $functionNode, $closureName)
    {
        $attributes = array();
        if ($functionNode->getDocComment()) {
            $attributes['comments'] = array($functionNode->getDocComment());
        }

        $closure = new Node\Expr\Assign(
            new Node\Expr\Variable($closureName),
            new Node\Expr\Closure(
                array(
                    'params' => $functionNode->params,
                    'stmts' => $functionNode->stmts,
                    'params' => $functionNode->params
                )
            ),
            $attributes
        );
        return $closure;
    }

    /**
     * @param $parameter
     * @return mixed
     */
    private function getDefaultValue(\ReflectionParameter $parameter)
    {
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        return null;
    }
}
