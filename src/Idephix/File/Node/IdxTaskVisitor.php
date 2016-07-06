<?php
namespace Idephix\File\Node;

use Idephix\IdxSetupCollector;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\PrettyPrinter\Standard;

class IdxTaskVisitor extends NodeVisitorAbstract
{
    /**
     * @var IdxSetupCollector
     */
    private $idxCollector;

    /**
     * @var Standard
     */
    private $codePrinter;

    public function __construct(IdxSetupCollector $collector)
    {
        $this->idxCollector = $collector;
        $this->codePrinter = new Standard();
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Function_) {
            $closureName = 'task';
            $closure = $this->convertFunctionToClosure($node, $closureName);

            eval($this->codePrinter->prettyPrint(array($closure)));

            $this->idxCollector->add(
                $this->cleanupTaskName($node),
                ${$closureName}
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
}
