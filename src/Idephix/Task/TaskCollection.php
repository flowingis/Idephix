<?php
namespace Idephix\Task;

use Idephix\Exception\InvalidIdxFileException;
use Idephix\Task\Parsing\IdxTaskVisitor;
use PhpParser\Lexer;
use PhpParser\Node\Name;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;

class TaskCollection extends Collection
{
    public static function ofTasks($array)
    {
        return new static(
            new \ArrayIterator(
                array_filter(
                    $array,
                    function ($task) {
                        return $task instanceof Task;
                    }
                )
            )
        );
    }

    public static function ofFunctions($idxFileContent)
    {
        $collection = static::dry();

        $parser = new Parser(new Lexer());
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolver());
        $traverser->addVisitor(new IdxTaskVisitor($collection));
        $stmts = $parser->parse($idxFileContent);
        $traverser->traverse($stmts);

        return $collection;
    }
    
    public static function parseFile($idxFile)
    {
        try {
            new \SplFileObject($idxFile);
        } catch (\RuntimeException $e) {
            throw new InvalidIdxFileException("$idxFile does not exists or is not readable");
        }

        return static::ofFunctions(file_get_contents($idxFile));
    }

    public function offsetSet($offset, $value)
    {
        if (!$value instanceof Task) {
            throw new \DomainException('TaskCollection can only accept \Idephix\Task\Task object');
        }

        $this->getInnerIterator()->offsetSet($offset, $value);
    }
}
