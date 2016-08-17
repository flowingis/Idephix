<?php
namespace Idephix\Task\Parsing;

use Idephix\Task\Parameter\Collection;
use Idephix\Task\CallableTask;
use Idephix\Task\TaskCollection;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\PrettyPrinter\Standard;

class TaskCollector extends NodeVisitorAbstract
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

            $this->collection[] = CallableTask::buildFromClosure($this->cleanupTaskName($node), ${$closureName});
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
}
