<?php

namespace Idephix\File\Node;

use Idephix\IdxSetupCollector;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\PrettyPrinter\Standard;

class IdxVariableVisitor extends NodeVisitorAbstract
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
        if($node instanceof Node\Expr\Assign){
            switch ($node->var->name) {
                case 'targets':
                    $this->collectVariable($node, 'setTargets');
                    break;
                case 'client':
                    $this->collectVariable($node, 'setSshClient');
                    break;
            }
        }
    }

    /**
     * @param Node $node
     */
    private function collectVariable(Node $node, $collectorMethod)
    {
        eval($this->codePrinter->prettyPrintExpr($node) . ';');
        call_user_func([$this->idxCollector, $collectorMethod], ${$node->var->name});

    }


}