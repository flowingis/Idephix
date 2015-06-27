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
        if($node instanceof Node\Stmt\Function_){

            $params = $this->codePrinter->prettyPrint($node->params);
            $code = $this->codePrinter->prettyPrint($node->stmts);

            /** @var string $taskCode */
            /** @var \Closure $task */
            $taskCode = sprintf('$task = function(%s){%s};', $params, $code);
            eval($taskCode);

            $this->idxCollector->add(
                $node->name,
                $task
            );
        }
    }
}