<?php

namespace Idephix\File\Node;

use Idephix\IdxSetupCollector;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\PrettyPrinter\Standard;

class IdxTargetVisitor extends NodeVisitorAbstract
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
            if($node->var->name == 'targets') {

                eval($this->codePrinter->prettyPrintExpr($node) . ';');

                $this->idxCollector->setTargets(${$node->var->name});
            }
        }
    }


}